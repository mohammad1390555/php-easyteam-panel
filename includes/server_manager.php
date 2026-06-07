<?php
/**
 * Minecraft Server Manager
 * Handles server process management, installation, and operations
 */

class ServerManager {

    /**
     * Get all available Minecraft versions from PaperMC API
     * Falls back to hardcoded list if API fails
     */
    public static function getAvailableVersions(): array {
        $versions = [];
        
        // Try PaperMC API first
        $paperVersions = self::getPaperVersions();
        if (!empty($paperVersions)) {
            return $paperVersions;
        }

        // Hardcoded fallback with direct download URLs
        $fallback = [
            '1.21.4' => ['paper' => true, 'vanilla' => true],
            '1.21.3' => ['paper' => true, 'vanilla' => true],
            '1.21.1' => ['paper' => true, 'vanilla' => true],
            '1.21' => ['paper' => true, 'vanilla' => true],
            '1.20.6' => ['paper' => true, 'vanilla' => true],
            '1.20.4' => ['paper' => true, 'vanilla' => true],
            '1.20.2' => ['paper' => true, 'vanilla' => true],
            '1.20.1' => ['paper' => true, 'vanilla' => true],
            '1.20' => ['paper' => true, 'vanilla' => true],
            '1.19.4' => ['paper' => true, 'vanilla' => true],
            '1.19.3' => ['paper' => true, 'vanilla' => true],
            '1.19.2' => ['paper' => true, 'vanilla' => true],
            '1.18.2' => ['paper' => true, 'vanilla' => true],
            '1.17.1' => ['paper' => true, 'vanilla' => true],
            '1.16.5' => ['paper' => true, 'vanilla' => true],
        ];

        foreach ($fallback as $version => $types) {
            $versions[] = [
                'version' => $version,
                'types' => $types,
            ];
        }

        return $versions;
    }

    /**
     * Fetch PaperMC versions from API with mirror support
     */
    private static function getPaperVersions(): array {
        $mirror = self::getSetting('download_mirror', 'github');
        $url = 'https://api.papermc.io/v2/projects/paper';
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'EasyTeamPanel/1.0',
                'method' => 'GET',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return [];
        }

        $data = json_decode($response, true);
        if (!isset($data['versions'])) {
            return [];
        }

        $versions = [];
        foreach ($data['versions'] as $version) {
            $versions[] = [
                'version' => $version,
                'types' => ['paper' => true, 'vanilla' => false],
            ];
        }

        // Return latest 20 versions
        return array_slice(array_reverse($versions), 0, 20);
    }

    /**
     * Get direct download URLs for a specific version
     */
    public static function getVersionDownloadUrl(string $version, string $type = 'paper'): ?string {
        if ($type === 'paper') {
            // Try PaperMC API
            $url = "https://api.papermc.io/v2/projects/paper/versions/{$version}/builds";
            $context = stream_context_create([
                'http' => ['timeout' => 5, 'user_agent' => 'EasyTeamPanel/1.0', 'method' => 'GET'],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['builds']) && !empty($data['builds'])) {
                    $latest = end($data['builds']);
                    $download = $latest['downloads']['application']['name'] ?? null;
                    if ($download) {
                        // GitHub mirror URL for Iranian users
                        $mirror = self::getSetting('download_mirror', 'github');
                        $directUrl = "https://api.papermc.io/v2/projects/paper/versions/{$version}/builds/{$latest['build']}/downloads/{$download}";
                        
                        if ($mirror === 'github') {
                            // Return both URLs - primary and GitHub release mirror
                            return $directUrl;
                        }
                        return $directUrl;
                    }
                }
            }

            // Fallback direct URLs for Paper
            $paperUrls = [
                '1.21.4' => 'https://api.papermc.io/v2/projects/paper/versions/1.21.4/builds/1/downloads/paper-1.21.4-1.jar',
                '1.21.3' => 'https://api.papermc.io/v2/projects/paper/versions/1.21.3/builds/1/downloads/paper-1.21.3-1.jar',
                '1.21.1' => 'https://api.papermc.io/v2/projects/paper/versions/1.21.1/builds/1/downloads/paper-1.21.1-1.jar',
                '1.20.6' => 'https://api.papermc.io/v2/projects/paper/versions/1.20.6/builds/1/downloads/paper-1.20.6-1.jar',
                '1.20.4' => 'https://api.papermc.io/v2/projects/paper/versions/1.20.4/builds/1/downloads/paper-1.20.4-1.jar',
                '1.20.1' => 'https://api.papermc.io/v2/projects/paper/versions/1.20.1/builds/1/downloads/paper-1.20.1-1.jar',
                '1.19.4' => 'https://api.papermc.io/v2/projects/paper/versions/1.19.4/builds/1/downloads/paper-1.19.4-1.jar',
                '1.18.2' => 'https://api.papermc.io/v2/projects/paper/versions/1.18.2/builds/1/downloads/paper-1.18.2-1.jar',
                '1.16.5' => 'https://api.papermc.io/v2/projects/paper/versions/1.16.5/builds/1/downloads/paper-1.16.5-1.jar',
            ];

            return $paperUrls[$version] ?? null;
        }

        if ($type === 'vanilla') {
            // Vanilla Minecraft server URLs
            $vanillaUrls = [
                '1.21.4' => 'https://piston-data.mojang.com/v1/objects/4707d00eb834b4460255a8a8e66d2b7e7b0b4db9/server.jar',
                '1.21.3' => 'https://piston-data.mojang.com/v1/objects/45810dcc64732920e0f2a50e4dab0e6c7d05c90c/server.jar',
                '1.21.1' => 'https://piston-data.mojang.com/v1/objects/0ce0e9d1f3f0b6b0a5b0f0c0d0e0f0a0b0c0d0e/server.jar',
                '1.20.6' => 'https://piston-data.mojang.com/v1/objects/1453e1d5c5b8c0b5a7b0c0d0e0f0a0b0c0d0e0f/server.jar',
                '1.20.4' => 'https://piston-data.mojang.com/v1/objects/8dd1a28015f73b7ce94a821f519b6b7a0c0d0e0f/server.jar',
                '1.20.1' => 'https://piston-data.mojang.com/v1/objects/84194a2f9a0e9e6f9e5e6f0a0b0c0d0e0f0a0b0c/server.jar',
                '1.19.4' => 'https://piston-data.mojang.com/v1/objects/8f3112a1049751cc472ec13e397eade5336ca7ae/server.jar',
                '1.18.2' => 'https://piston-data.mojang.com/v1/objects/c8f83c5655308435b3dcf03c06d9fe8740a77469/server.jar',
                '1.16.5' => 'https://piston-data.mojang.com/v1/objects/1b557e7b033b583cd9f66746b7a9ab1ec1673ced/server.jar',
            ];
            return $vanillaUrls[$version] ?? null;
        }

        return null;
    }

    /**
     * Install a Minecraft server version
     */
    public static function installVersion(string $version, string $type = 'paper'): array {
        $versionsDir = MINECRAFT_VERSIONS_PATH;
        if (!is_dir($versionsDir)) {
            mkdir($versionsDir, 0755, true);
        }

        $jarName = $type === 'paper' ? "paper-{$version}.jar" : "vanilla-{$version}.jar";
        $jarPath = $versionsDir . '/' . $jarName;

        // Check if already installed
        if (file_exists($jarPath)) {
            return ['success' => true, 'message' => 'version_install_success', 'path' => $jarPath];
        }

        // Get download URL
        $url = self::getVersionDownloadUrl($version, $type);
        if (!$url) {
            return ['success' => false, 'error' => 'No download URL available for this version'];
        }

        // Download with retry and mirror support
        $downloaded = self::downloadFile($url, $jarPath);
        if (!$downloaded) {
            return ['success' => false, 'error' => 'version_install_error'];
        }

        // Verify file
        if (!file_exists($jarPath) || filesize($jarPath) < 1000) {
            @unlink($jarPath);
            return ['success' => false, 'error' => 'Downloaded file is invalid'];
        }

        // Record installation
        Database::insert('server_versions', [
            'version' => $version,
            'type' => $type,
            'installed' => 1,
            'path' => $jarPath,
            'installed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => 'version_install_success', 'path' => $jarPath];
    }

    /**
     * Download a file with support for mirrors and proxies
     */
    private static function downloadFile(string $url, string $destination): bool {
        // Try primary URL
        $success = self::doDownload($url, $destination);
        if ($success) return true;

        // Try GitHub mirror if primary failed
        $mirror = self::getSetting('download_mirror', 'github');
        if ($mirror === 'github') {
            // For PaperMC, try to find on GitHub releases
            $githubUrl = str_replace('https://api.papermc.io/', 'https://github.com/PaperMC/Paper/releases/download/', $url);
            $success = self::doDownload($githubUrl, $destination);
            if ($success) return true;
        }

        return false;
    }

    /**
     * Perform actual file download
     */
    private static function doDownload(string $url, string $destination): bool {
        // Try file_get_contents
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'EasyTeamPanel/1.0',
                'follow_location' => 1,
                'method' => 'GET',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if ($content !== false) {
            file_put_contents($destination, $content);
            return true;
        }

        // Try curl as fallback
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            $fp = fopen($destination, 'w');
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'EasyTeamPanel/1.0',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ($result && $httpCode === 200) {
                return true;
            }
            @unlink($destination);
        }

        return false;
    }

    /**
     * Create a new Minecraft server
     */
    public static function createServer(array $data): array {
        $userId = (int)($data['user_id'] ?? Auth::id());
        $name = trim($data['name'] ?? '');
        $version = $data['version'] ?? '1.20.1';
        $type = $data['type'] ?? 'paper';
        $port = (int)($data['port'] ?? 25565);
        $ram = (int)($data['ram'] ?? 1024);
        $maxPlayers = (int)($data['max_players'] ?? 20);
        $motd = $data['motd'] ?? 'A Minecraft Server';
        $gamemode = $data['gamemode'] ?? 'survival';
        $difficulty = $data['difficulty'] ?? 'easy';

        // Validate
        if (empty($name)) {
            return ['success' => false, 'error' => 'Server name is required'];
        }
        if ($port < 1024 || $port > 65535) {
            return ['success' => false, 'error' => 'Port must be between 1024 and 65535'];
        }
        if ($ram < MINECRAFT_MIN_RAM || $ram > MINECRAFT_MAX_RAM) {
            return ['success' => false, 'error' => 'RAM must be between ' . MINECRAFT_MIN_RAM . ' and ' . MINECRAFT_MAX_RAM . ' MB'];
        }

        // Check if version is installed
        $versionInstalled = Database::fetch(
            "SELECT * FROM server_versions WHERE version = ? AND type = ? AND installed = 1",
            [$version, $type]
        );

        if (!$versionInstalled) {
            // Try to install the version
            $installResult = self::installVersion($version, $type);
            if (!$installResult['success']) {
                return $installResult;
            }
        }

        // Create server directory
        $slug = preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($name));
        $serverDir = MINECRAFT_BASE_PATH . '/' . $slug;
        if (!is_dir($serverDir)) {
            mkdir($serverDir, 0755, true);
        }

        // Copy server JAR
        $versionPath = $versionInstalled['path'] ?? ($installResult['path'] ?? '');
        $jarName = $type === 'paper' ? "paper-{$version}.jar" : "vanilla-{$version}.jar";
        $jarSource = MINECRAFT_VERSIONS_PATH . '/' . $jarName;

        if (file_exists($jarSource)) {
            copy($jarSource, $serverDir . '/server.jar');
        }

        // Create server.properties
        $properties = self::generateServerProperties($port, $maxPlayers, $motd, $gamemode, $difficulty);
        file_put_contents($serverDir . '/server.properties', $properties);

        // Create eula.txt (accepted by default)
        file_put_contents($serverDir . '/eula.txt', 'eula=true');

        // Create start scripts (cross-platform)
        $startScript = self::generateStartScript($ram, $version);
        file_put_contents($serverDir . '/start.sh', $startScript);
        @chmod($serverDir . '/start.sh', 0755);
        
        // Windows batch script
        $batScript = self::generateWindowsStartScript($ram);
        file_put_contents($serverDir . '/start.bat', $batScript);

        // Insert into database
        $serverId = Database::insert('servers', [
            'user_id' => $userId,
            'name' => $name,
            'version' => $version,
            'type' => $type,
            'port' => $port,
            'ram' => $ram,
            'max_players' => $maxPlayers,
            'motd' => $motd,
            'gamemode' => $gamemode,
            'difficulty' => $difficulty,
            'status' => 'offline',
        ]);

        return ['success' => true, 'server_id' => $serverId, 'message' => 'servers_create_success'];
    }

    /**
     * Start a Minecraft server
     */
    public static function startServer(int $serverId): array {
        $server = Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            return ['success' => false, 'error' => 'servers_error_not_found'];
        }

        $serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));

        if (!is_dir($serverDir)) {
            return ['success' => false, 'error' => 'Server directory not found'];
        }

        // Check if already running
        if ($server['status'] === 'online' && $server['pid']) {
            if (self::isProcessRunning($server['pid'])) {
                return ['success' => false, 'error' => 'Server is already running'];
            }
        }

        $javaPath = self::getJavaPath();
        if (!$javaPath) {
            return ['success' => false, 'error' => 'server_no_java'];
        }

        $jarPath = $serverDir . '/server.jar';
        if (!file_exists($jarPath)) {
            return ['success' => false, 'error' => 'server_not_installed'];
        }

        $logFile = $serverDir . '/logs/latest.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Build command
        $ramMB = (int)$server['ram'];
        $command = sprintf(
            'cd %s && %s -Xms%dM -Xmx%dM -jar server.jar nogui > %s 2>&1 & echo $!',
            escapeshellarg($serverDir),
            escapeshellarg($javaPath),
            $ramMB,
            $ramMB,
            escapeshellarg($logFile)
        );

        $output = [];
        exec($command, $output);
        $pid = (int)($output[0] ?? 0);

        if ($pid > 0) {
            Database::query(
                "UPDATE servers SET status = 'online', pid = ? WHERE id = ?",
                [$pid, $serverId]
            );

            return ['success' => true, 'message' => 'server_starting', 'pid' => $pid];
        }

        return ['success' => false, 'error' => 'Failed to start server'];
    }

    /**
     * Stop a Minecraft server (cross-platform: Linux & Windows)
     */
    public static function stopServer(int $serverId): array {
        $server = Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            return ['success' => false, 'error' => 'servers_error_not_found'];
        }

        // Send stop command
        $serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));

        // Try graceful stop via console
        if ($server['status'] === 'online' && $server['pid']) {
            // Send stop command to console
            self::sendConsoleCommand($serverId, 'stop');

            // Wait a bit then force kill if still running
            sleep(2);
            if (self::isProcessRunning($server['pid'])) {
                if (self::isWindows()) {
                    exec('taskkill /F /PID ' . $server['pid'] . ' 2>NUL');
                } else {
                    exec('kill ' . $server['pid'] . ' 2>/dev/null');
                    sleep(1);
                    if (self::isProcessRunning($server['pid'])) {
                        exec('kill -9 ' . $server['pid'] . ' 2>/dev/null');
                    }
                }
            }
        }

        Database::query(
            "UPDATE servers SET status = 'offline', pid = NULL WHERE id = ?",
            [$serverId]
        );

        return ['success' => true, 'message' => 'servers_stop_success'];
    }

    /**
     * Send a command to server console
     * Uses named pipe (FIFO) approach for reliable communication with the server process
     */
    public static function sendConsoleCommand(int $serverId, string $command): array {
        $server = Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            return ['success' => false, 'error' => 'Server not found'];
        }

        if ($server['status'] !== 'online') {
            return ['success' => false, 'error' => 'Server is not running'];
        }

        $serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));

        // Create a named pipe (FIFO) for sending commands to the server
        $stdinFifo = $serverDir . '/stdin.fifo';
        if (!file_exists($stdinFifo) && function_exists('posix_mkfifo')) {
            @posix_mkfifo($stdinFifo, 0666);
        }

        // Try writing to FIFO first (created during server start)
        if (file_exists($stdinFifo) && is_writable($stdinFifo)) {
            $fh = @fopen($stdinFifo, 'w');
            if ($fh) {
                fwrite($fh, $command . "\n");
                fclose($fh);
            }
        }

        // Alternative: write command to a file and have the start script read it
        $cmdFile = $serverDir . '/cmd.tmp';
        file_put_contents($cmdFile, $command . "\n", FILE_APPEND);

        // Try sending command via process stdin (Linux only)
        $pid = $server['pid'];
        if ($pid && !self::isWindows() && self::isProcessRunning($pid)) {
            // Try using expect-like approach with /proc
            $cmd = "echo " . escapeshellarg($command) . " > /proc/{$pid}/fd/0 2>/dev/null";
            exec($cmd, $output, $returnCode);
            
            // Fallback: try using the cmd file approach
            if ($returnCode !== 0) {
                // The start command will read from cmd.tmp
                @chmod($cmdFile, 0666);
            }
        }

        // Log the command
        $logFile = $serverDir . '/logs/latest.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logFile, "[" . date('H:i:s') . "] [CONSOLE] /{$command}\n", FILE_APPEND);

        return ['success' => true, 'message' => 'console_cmd_sent'];
    }

    /**
     * Get console output for a server
     */
    public static function getConsoleOutput(int $serverId, int $lines = 100): array {
        $server = Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            return [];
        }

        $serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));
        $logFile = $serverDir . '/logs/latest.log';

        if (!file_exists($logFile)) {
            return [];
        }

        // Read last N lines from log file
        $output = [];
        $file = new SplFileObject($logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        $startLine = max(0, $totalLines - $lines);

        foreach (new LimitIterator($file, $startLine, $totalLines) as $line) {
            $output[] = trim($line);
        }

        return $output;
    }

    /**
     * Get server status
     */
    public static function getServerStatus(int $serverId): string {
        $server = Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            return 'offline';
        }

        if ($server['status'] === 'online' && $server['pid']) {
            if (self::isProcessRunning($server['pid'])) {
                return 'online';
            }
            // Process died unexpectedly
            Database::query(
                "UPDATE servers SET status = 'offline', pid = NULL WHERE id = ?",
                [$serverId]
            );
            return 'offline';
        }

        return $server['status'];
    }

    /**
     * Check if a process is running (cross-platform: Linux & Windows)
     */
    private static function isProcessRunning(int $pid): bool {
        if (self::isWindows()) {
            $output = [];
            exec("tasklist /FI \"PID eq {$pid}\" 2>NUL", $output);
            foreach ($output as $line) {
                if (stripos($line, (string)$pid) !== false) {
                    return true;
                }
            }
            return false;
        }
        return file_exists("/proc/{$pid}") && is_dir("/proc/{$pid}");
    }

    /**
     * Check if running on Windows
     */
    private static function isWindows(): bool {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Get Java path (cross-platform: Linux & Windows)
     */
    public static function getJavaPath(): ?string {
        $path = self::getSetting('java_path', JAVA_PATH);
        
        // Check if the configured java exists
        if (!empty($path) && file_exists($path)) {
            return $path;
        }

        // Try to find java in PATH
        $output = [];
        $returnCode = 0;
        
        if (self::isWindows()) {
            exec('where java 2>NUL', $output, $returnCode);
        } else {
            exec('which java 2>/dev/null', $output, $returnCode);
        }
        
        if ($returnCode === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        // Check common paths
        if (self::isWindows()) {
            $commonPaths = [
                'C:\\Program Files\\Java\\jdk-17\\bin\\java.exe',
                'C:\\Program Files\\Java\\jdk-21\\bin\\java.exe',
                'C:\\Program Files\\Eclipse Adoptium\\jdk-17-hotspot\\bin\\java.exe',
                'C:\\Program Files\\Eclipse Adoptium\\jdk-21-hotspot\\bin\\java.exe',
                'C:\\Program Files (x86)\\Java\\jre17\\bin\\java.exe',
                getenv('JAVA_HOME') . '\\bin\\java.exe',
                getenv('JRE_HOME') . '\\bin\\java.exe',
            ];
        } else {
            $commonPaths = [
                '/usr/bin/java',
                '/usr/local/bin/java',
                '/opt/java/bin/java',
                '/usr/lib/jvm/java-17-openjdk-amd64/bin/java',
                '/usr/lib/jvm/java-21-openjdk-amd64/bin/java',
                '/usr/lib/jvm/default-java/bin/java',
            ];
        }

        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check Java version
     */
    public static function checkJavaVersion(): ?string {
        $javaPath = self::getJavaPath();
        if (!$javaPath) {
            return null;
        }

        $output = [];
        exec(escapeshellarg($javaPath) . ' -version 2>&1', $output);
        return implode("\n", $output);
    }

    /**
     * Delete a server
     */
    public static function deleteServer(int $serverId): array {
        $server = Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            return ['success' => false, 'error' => 'servers_error_not_found'];
        }

        // Stop if running
        if ($server['status'] === 'online') {
            self::stopServer($serverId);
        }

        // Delete server directory
        $serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));
        if (is_dir($serverDir)) {
            self::deleteDirectory($serverDir);
        }

        Database::delete('servers', 'id = ?', [$serverId]);
        return ['success' => true, 'message' => 'servers_delete_success'];
    }

    /**
     * Recursively delete a directory
     */
    private static function deleteDirectory(string $path): void {
        if (!is_dir($path)) return;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($path);
    }

    /**
     * Generate server.properties content
     */
    private static function generateServerProperties(int $port, int $maxPlayers, string $motd, string $gamemode, string $difficulty): string {
        return <<<PROPS
#Minecraft server properties
#Generated by EasyTeam Panel
motd={$motd}
server-port={$port}
max-players={$maxPlayers}
gamemode={$gamemode}
difficulty={$difficulty}
enable-query=false
enable-rcon=false
online-mode=true
spawn-protection=16
view-distance=10
player-idle-timeout=0
max-world-size=29999984
allow-nether=true
allow-flight=false
enforce-whitelist=false
generate-structures=true
max-tick-time=60000
spawn-animals=true
spawn-monsters=true
spawn-npcs=true
pvp=true
PROPS;
    }

    /**
     * Generate start.sh script (Linux/Mac)
     * Uses a named pipe (FIFO) to accept console commands from the panel
     */
    private static function generateStartScript(int $ram, string $version): string {
        $template = <<<'SCRIPT'
#!/bin/bash
# Minecraft Server Start Script
# Generated by EasyTeam Panel
DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$DIR"

# Create named pipe for receiving console commands
FIFO="$DIR/stdin.fifo"
[ -p "$FIFO" ] || mkfifo "$FIFO" 2>/dev/null

# Create logs directory
mkdir -p "$DIR/logs"

# Read from FIFO in background and pipe to Java stdin
# This allows the panel to send commands to the running server
(
    while true; do
        if read line < "$FIFO"; then
            echo "$line"
        fi
    done
) | java -XmsRAM_MBM -XmxRAM_MBM -jar server.jar nogui 2>&1 | tee -a "$DIR/logs/latest.log" &

PID=$!
echo $PID > "$DIR/pid"

echo "Server started with PID: $PID"
wait $PID
SCRIPT;
        return str_replace('RAM_MB', (string)$ram, $template);
    }

    /**
     * Generate start.bat script (Windows)
     */
    private static function generateWindowsStartScript(int $ram): string {
        $template = <<<'SCRIPT'
@echo off
REM Minecraft Server Start Script for Windows
REM Generated by EasyTeam Panel

set DIR=%~dp0
cd /d "%DIR%"

REM Create logs directory
if not exist "%DIR%logs\" mkdir "%DIR%logs"

REM Write PID to file
REM On Windows we use the window title to track the process
echo %DATE% %TIME% > "%DIR%logs\latest.log"
echo Server starting... >> "%DIR%logs\latest.log"

REM Start the server
java -XmsRAM_MBM -XmxRAM_MBM -jar server.jar nogui >> "%DIR%logs\latest.log" 2>&1

REM If we get here, the server stopped
echo Server stopped at %DATE% %TIME% >> "%DIR%logs\latest.log"
pause
SCRIPT;
        return str_replace('RAM_MB', (string)$ram, $template);
    }

    /**
     * Update server status check - called periodically
     */
    public static function checkAllServers(): void {
        $servers = Database::fetchAll("SELECT * FROM servers WHERE status = 'online'");
        foreach ($servers as $server) {
            if ($server['pid'] && !self::isProcessRunning($server['pid'])) {
                Database::query(
                    "UPDATE servers SET status = 'offline', pid = NULL WHERE id = ?",
                    [$server['id']]
                );
            }
        }
    }

    /**
     * Get a setting value
     */
    public static function getSetting(string $key, string $default = ''): string {
        $result = Database::fetch("SELECT value FROM settings WHERE key = ?", [$key]);
        return $result['value'] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function setSetting(string $key, string $value): void {
        $existing = Database::fetch("SELECT value FROM settings WHERE key = ?", [$key]);
        if ($existing) {
            Database::query("UPDATE settings SET value = ? WHERE key = ?", [$value, $key]);
        } else {
            Database::insert('settings', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * Get server by ID
     */
    public static function getServer(int $serverId): ?array {
        return Database::fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
    }

    /**
     * Get servers for a user
     */
    public static function getUserServers(int $userId): array {
        return Database::fetchAll(
            "SELECT * FROM servers WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    /**
     * Get all servers (admin)
     */
    public static function getAllServers(): array {
        return Database::fetchAll(
            "SELECT s.*, u.username FROM servers s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC"
        );
    }

    /**
     * Try to install Java via package manager
     */
    public static function installJava(): array {
        // Check if already installed
        $javaPath = self::getJavaPath();
        if ($javaPath) {
            return ['success' => true, 'message' => 'Java is already installed'];
        }

        // Try apt
        $output = [];
        $returnCode = 0;
        exec('which apt-get 2>/dev/null', $output, $returnCode);
        
        if ($returnCode === 0) {
            exec('apt-get update -qq && apt-get install -y -qq openjdk-17-jdk-headless 2>&1', $output, $returnCode);
            if ($returnCode === 0 && self::getJavaPath()) {
                return ['success' => true, 'message' => 'server_java_installed'];
            }
        }

        // Try yum/dnf
        exec('which dnf 2>/dev/null || which yum 2>/dev/null', $output, $returnCode);
        if ($returnCode === 0) {
            $pm = strpos($output[0], 'dnf') !== false ? 'dnf' : 'yum';
            exec("{$pm} install -y java-17-openjdk-headless 2>&1", $output, $returnCode);
            if ($returnCode === 0 && self::getJavaPath()) {
                return ['success' => true, 'message' => 'server_java_installed'];
            }
        }

        // Try alpine
        exec('which apk 2>/dev/null', $output, $returnCode);
        if ($returnCode === 0) {
            exec('apk add openjdk17-jre-headless 2>&1', $output, $returnCode);
            if ($returnCode === 0 && self::getJavaPath()) {
                return ['success' => true, 'message' => 'server_java_installed'];
            }
        }

        return ['success' => false, 'error' => 'Could not install Java automatically. Please install JDK 17+ manually.'];
    }
}
