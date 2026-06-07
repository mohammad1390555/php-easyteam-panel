<?php
/**
 * CuteCaptcha - Self-hosted anti-bot system
 * 
 * A simple, cute, and fully offline CAPTCHA system.
 * No external dependencies - works even without internet access.
 * Perfect for Iranian users (Google reCAPTCHA is blocked).
 * 
 * Challenge types:
 *   - emoji_count : "How many X do you see?" 
 *   - simple_math : "What is X + Y?"
 *   - emoji_math  : "Count the total emojis"
 */

class CuteCaptcha {

    /**
     * Generate a new captcha challenge and store it in the session.
     * Returns the challenge data for rendering.
     */
    public static function generate(): array {
        $types = ['emoji_count', 'simple_math', 'emoji_math'];
        $type = $types[array_rand($types)];
        
        $challenge = [];
        $answer = 0;
        $options = [];
        
        switch ($type) {
            case 'emoji_count':
                $emojis = ['🌟', '⭐', '💎', '🌸', '🍀', '🎀', '💫', '✨', '🦋', '🌈'];
                $emoji = $emojis[array_rand($emojis)];
                $count = rand(3, 7);
                $display = str_repeat($emoji . ' ', $count);
                $answer = $count;
                $challenge = [
                    'type' => 'emoji_count',
                    'question' => $emoji,
                    'display' => trim($display),
                    'label_key' => 'captcha_emoji_count',
                ];
                // Generate options: correct answer + 3 distractors
                $options = self::generateOptions($answer, 1, 10);
                break;
                
            case 'simple_math':
                $a = rand(2, 9);
                $b = rand(2, 9);
                $ops = ['+', '−'];
                $op = $ops[array_rand($ops)];
                
                if ($op === '+') {
                    $answer = $a + $b;
                    $display = "$a + $b";
                } else {
                    // Ensure positive result
                    if ($a < $b) [$a, $b] = [$b, $a];
                    $answer = $a - $b;
                    $display = "$a − $b";
                }
                
                $challenge = [
                    'type' => 'simple_math',
                    'display' => $display,
                    'label_key' => 'captcha_simple_math',
                ];
                $options = self::generateOptions($answer, 1, 18);
                break;
                
            case 'emoji_math':
                $emojis = ['🐱', '🐶', '🐰', '🐼', '🦊', '🐸'];
                $emoji = $emojis[array_rand($emojis)];
                $a = rand(2, 5);
                $b = rand(2, 5);
                $answer = $a + $b;
                $display = str_repeat($emoji, $a) . ' + ' . str_repeat($emoji, $b);
                $challenge = [
                    'type' => 'emoji_math',
                    'emoji' => $emoji,
                    'display' => $display,
                    'label_key' => 'captcha_emoji_math',
                ];
                $options = self::generateOptions($answer, 2, 12);
                break;
        }
        
        // Shuffle options
        shuffle($options);
        
        // Store answer in session
        $_SESSION['captcha_answer'] = $answer;
        $_SESSION['captcha_created'] = time();
        
        $challenge['options'] = $options;
        $challenge['id'] = substr(md5($answer . session_id()), 0, 8);
        
        return $challenge;
    }
    
    /**
     * Generate multiple choice options including correct answer
     */
    private static function generateOptions(int $correct, int $min, int $max): array {
        $options = [$correct];
        $attempts = 0;
        
        while (count($options) < 4 && $attempts < 50) {
            $offset = rand(1, max(3, intval($max / 3)));
            $distractor = $correct + (rand(0, 1) ? $offset : -$offset);
            
            // Keep within bounds
            if ($distractor < $min) $distractor = $correct + $offset;
            if ($distractor > $max) $distractor = $correct - $offset;
            if ($distractor < $min || $distractor > $max) continue;
            
            if (!in_array($distractor, $options)) {
                $options[] = $distractor;
            }
            $attempts++;
        }
        
        // Fill remaining if needed
        while (count($options) < 4) {
            $v = rand($min, $max);
            if (!in_array($v, $options)) {
                $options[] = $v;
            }
        }
        
        return $options;
    }
    
    /**
     * Verify the user's answer against the stored session value.
     */
    public static function verify(string $answer): bool {
        // Check if captcha exists and hasn't expired (10 minutes)
        if (!isset($_SESSION['captcha_answer']) || !isset($_SESSION['captcha_created'])) {
            return false;
        }
        
        $elapsed = time() - $_SESSION['captcha_created'];
        if ($elapsed > 600) { // 10 minutes expiry
            self::clear();
            return false;
        }
        
        $correct = (int)$_SESSION['captcha_answer'];
        $userAnswer = (int)$answer;
        
        self::clear();
        
        return $correct === $userAnswer;
    }
    
    /**
     * Clear captcha data from session
     */
    public static function clear(): void {
        unset($_SESSION['captcha_answer']);
        unset($_SESSION['captcha_created']);
    }
    
    /**
     * Render the captcha widget as HTML
     */
    public static function render(): string {
        $challenge = self::generate();
        $label = __($challenge['label_key']);
        
        $html = '<div class="cute-captcha" id="cuteCaptcha">';
        $html .= '<div class="captcha-inner">';
        
        // Captcha header with cute icon
        $html .= '<div class="captcha-header">';
        $html .= '<svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-shield"/></svg>';
        $html .= '<span class="captcha-title">' . __('captcha_title') . '</span>';
        $html .= '</div>';
        
        // Challenge display
        $html .= '<div class="captcha-challenge">';
        
        if ($challenge['type'] === 'emoji_count') {
            $html .= '<div class="captcha-question">';
            $html .= '<span class="captcha-label">' . $label . '</span>';
            $html .= '</div>';
            $html .= '<div class="captcha-emojis">' . htmlspecialchars($challenge['display']) . '</div>';
        } elseif ($challenge['type'] === 'simple_math') {
            $html .= '<div class="captcha-question">';
            $html .= '<span class="captcha-label">' . $label . '</span>';
            $html .= '</div>';
            $html .= '<div class="captcha-math">' . htmlspecialchars($challenge['display']) . ' = ?</div>';
        } elseif ($challenge['type'] === 'emoji_math') {
            $html .= '<div class="captcha-question">';
            $html .= '<span class="captcha-label">' . $label . '</span>';
            $html .= '</div>';
            $html .= '<div class="captcha-emoji-math">' . $challenge['display'] . ' = ?</div>';
        }
        
        $html .= '</div>';
        
        // Options (multiple choice buttons)
        $html .= '<div class="captcha-options" data-captcha-id="' . htmlspecialchars($challenge['id']) . '">';
        foreach ($challenge['options'] as $idx => $option) {
            $html .= '<button type="button" class="captcha-option" data-value="' . $option . '" onclick="selectCaptcha(this)">';
            $html .= $option;
            $html .= '</button>';
        }
        $html .= '</div>';
        
        // Hidden input for the selected answer
        $html .= '<input type="hidden" name="captcha_answer" id="captchaAnswer" value="">';
        
        // Feedback area
        $html .= '<div class="captcha-feedback" id="captchaFeedback" style="display:none;">';
        $html .= '<svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg>';
        $html .= '<span id="captchaFeedbackText"></span>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
