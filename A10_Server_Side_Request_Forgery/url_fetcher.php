<?php
// VULNERABILITÀ CRITICA: Server-Side Request Forgery (SSRF)
// Questo file contiene funzioni vulnerabili che permettono SSRF

class URLFetcher {
    
    // VULNERABILITÀ CRITICA: Fetch URL senza validazione
    // Permette accesso a localhost, IP privati, metadata services, etc.
    
    public static function fetch($url) {
        // VULNERABILITÀ: Nessuna validazione dell'URL!
        // Un attaccante può inserire:
        // - http://localhost/admin
        // - http://127.0.0.1:3306 (MySQL)
        // - http://169.254.169.254/latest/meta-data/ (AWS metadata)
        // - http://192.168.1.1/admin
        // - file:///etc/passwd
        
        $start_time = microtime(true);
        
        // VULNERABILITÀ: Usa file_get_contents senza validazione
        // file_get_contents supporta http://, https://, file://, ftp://, etc.
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'URL Preview Bot',
                'follow_location' => true,  // VULNERABILITÀ: Segue redirect senza validazione!
                'max_redirects' => 5
            ]
        ]);
        
        // VULNERABILITÀ CRITICA: Fetch senza validazione URL
        $content = @file_get_contents($url, false, $context);
        
        $response_time = round((microtime(true) - $start_time) * 1000);
        
        if ($content === false) {
            return [
                'success' => false,
                'error' => 'Failed to fetch URL',
                'response_time' => $response_time
            ];
        }
        
        return [
            'success' => true,
            'content' => $content,
            'response_time' => $response_time,
            'headers' => isset($http_response_header) ? $http_response_header : []
        ];
    }
    
    // VULNERABILITÀ: Fetch con cURL senza validazione
    public static function fetchWithCurl($url) {
        // VULNERABILITÀ: cURL supporta molti protocolli pericolosi
        // gopher://, dict://, file://, etc.
        
        $ch = curl_init();
        
        // VULNERABILITÀ: Nessuna validazione URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // VULNERABILITÀ: Segue redirect!
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'URL Preview Bot');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // VULNERABILITÀ: Non verifica SSL!
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // VULNERABILITÀ: Permette protocolli pericolosi
        // curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);  // NON IMPLEMENTATO!
        
        $content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000;
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($content === false || !empty($error)) {
            return [
                'success' => false,
                'error' => $error ?: 'Failed to fetch URL',
                'http_code' => $http_code,
                'response_time' => $response_time
            ];
        }
        
        return [
            'success' => true,
            'content' => $content,
            'http_code' => $http_code,
            'response_time' => $response_time
        ];
    }
    
    // VULNERABILITÀ: Filtro debole che può essere bypassato
    public static function isURLAllowed($url) {
        // VULNERABILITÀ: Filtro molto debole che può essere bypassato facilmente
        
        // Blacklist debole
        $blacklist = ['localhost', '127.0.0.1', '0.0.0.0'];
        
        // VULNERABILITÀ: Bypassabile con:
        // - http://127.0.0.1 → http://127.1 (shortened)
        // - http://localhost → http://localtest.me (DNS rebinding)
        // - http://[::1] (IPv6)
        // - http://2130706433 (decimal IP)
        // - http://0x7f000001 (hex IP)
        // - URL encoding: http://127.0.0.1 → http://127.0.0.%31
        // - Double encoding
        
        foreach ($blacklist as $blocked) {
            if (stripos($url, $blocked) !== false) {
                return false;
            }
        }
        
        // VULNERABILITÀ: Non controlla:
        // - IP privati (10.0.0.0/8, 192.168.0.0/16, 172.16.0.0/12)
        // - Metadata services (169.254.169.254)
        // - Protocolli pericolosi (file://, gopher://, dict://)
        // - IPv6
        // - Decimal/Hex IP representation
        
        return true;
    }
    
    // VULNERABILITÀ: Estrae metadati senza validazione
    public static function extractMetadata($content, $url) {
        $metadata = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];
        
        // VULNERABILITÀ: Parsing HTML senza sanitizzazione
        if (preg_match('/<title>(.*?)<\/title>/is', $content, $matches)) {
            $metadata['title'] = html_entity_decode($matches[1]);
        }
        
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $content, $matches)) {
            $metadata['description'] = html_entity_decode($matches[1]);
        }
        
        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/is', $content, $matches)) {
            $metadata['image'] = $matches[1];
        }
        
        return $metadata;
    }
}

