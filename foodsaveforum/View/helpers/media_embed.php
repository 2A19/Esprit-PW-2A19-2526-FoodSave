<?php
if (!function_exists('extractYouTubeId')) {
    function extractYouTubeId($url) {
        $parts = parse_url($url);
        if (!$parts || !isset($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';

        if (strpos($host, 'youtu.be') !== false) {
            $id = trim($path, '/');
            return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) ? $id : null;
        }

        if (strpos($host, 'youtube.com') !== false) {
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                if (!empty($query['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $query['v'])) {
                    return $query['v'];
                }
            }

            if (preg_match('~/(embed|shorts)/([a-zA-Z0-9_-]{11})~', $path, $matches)) {
                return $matches[2];
            }
        }

        return null;
    }
}

if (!function_exists('renderContentWithEmbeds')) {
    function renderContentWithEmbeds($content) {
        $content = (string) $content;
        $tokens = preg_split('~(https?://[^\s<]+)~i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';

        foreach ($tokens as $token) {
            if (preg_match('~^https?://~i', $token)) {
                $url = trim($token);
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $youtubeId = extractYouTubeId($url);

                if ($youtubeId) {
                    $html .= '<div class="embedded-media embedded-video">';
                    $html .= '<iframe src="https://www.youtube-nocookie.com/embed/' . $youtubeId . '" ';
                    $html .= 'title="YouTube video player" loading="lazy" allowfullscreen ';
                    $html .= 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>';
                    $html .= '</div>';
                    continue;
                }

                if (preg_match('~\.gif(\?.*)?$~i', $url)) {
                    $html .= '<div class="embedded-media embedded-gif">';
                    $html .= '<img src="' . $safeUrl . '" alt="GIF media" loading="lazy">';
                    $html .= '</div>';
                    continue;
                }

                $html .= '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $safeUrl . '</a>';
                continue;
            }

            $html .= nl2br(htmlspecialchars($token, ENT_QUOTES, 'UTF-8'), false);
        }

        return $html;
    }
}
?>
