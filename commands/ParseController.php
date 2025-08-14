<?php

namespace app\commands;

use yii\console\Controller;
use app\models\LogEntry;
use SplFileObject;

class ParseController extends Controller
{
    public function actionIndex($file)
    {
        if (!file_exists($file)) {
            echo "Файл не найден: $file\n";
            return 1;
        }

        $file = new SplFileObject($file);
        $count = 0;
        $errors = 0;

        foreach ($file as $lineNumber => $line) {
            if (trim($line) === '') {
                continue;
            }

            $data = $this->parseLine($line);
            if ($data) {
                $log = new LogEntry();
                $log->setAttributes($data, false);
                if ($log->save()) {
                    $count++;
                } else {
                    echo "Ошибка сохранения в строке " . ($lineNumber + 1) . ": " . print_r($log->errors, true) . "\n";
                    $errors++;
                }
            } else {
                echo "Не удалось распарсить строку " . ($lineNumber + 1) . ": $line\n";
                $errors++;
            }

            if ($count % 1000 === 0) {
                echo "Обработано: $count строк\n";
            }
        }

        echo "Парсинг завершен. Успешно: $count, Ошибок: $errors\n";
        return 0;
    }

    private function parseLine($line)
    {
        $pattern = '/^(\S+) \S+ \S+ \[([^\]]+)\] "(\S+) ([^"]+)" \d+ \d+ "([^"]*)" "([^"]*)"/';
        
        if (!preg_match($pattern, trim($line), $matches)) {
            return null;
        }

        try {
            $ip = $matches[1];
            $timestampStr = $matches[2];
            $method = $matches[3];
            $url = $matches[4];
            $referer = $matches[5];
            $userAgent = $matches[6];

            $timestamp = \DateTime::createFromFormat('d/M/Y:H:i:s O', $timestampStr);
            if (!$timestamp) {
                echo "Ошибка парсинга даты: $timestampStr\n";
                return null;
            }

            $parsedUA = $this->parseUserAgent($userAgent);

            return [
                'ip' => $ip,
                'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                'url' => $url,
                'user_agent' => $userAgent,
                'os' => $parsedUA['os'],
                'architecture' => $parsedUA['arch'],
                'browser' => $parsedUA['browser'],
            ];
        } catch (\Exception $e) {
            echo "Ошибка обработки строки: " . $e->getMessage() . "\n";
            return null;
        }
    }

    private function parseUserAgent($ua)
    {
        $os = 'Unknown';
        $arch = 'Unknown';
        $browser = 'Unknown';

        if (stripos($ua, 'Googlebot') !== false) {
            $browser = 'Googlebot';
            if (stripos($ua, 'Image') !== false) $browser = 'Googlebot-Image';
            if (stripos($ua, 'Video') !== false) $browser = 'Googlebot-Video';
            if (stripos($ua, 'Mobile') !== false) $browser = 'Googlebot-Mobile';
            $os = 'Bot';
            $arch = 'N/A';
            return compact('os', 'arch', 'browser');
        }

        if ((stripos($ua, 'Yandex') !== false && stripos($ua, 'Bot') !== false) || 
            stripos($ua, 'YandexBot') !== false) {
            $browser = 'YandexBot';
            $os = 'Bot';
            $arch = 'N/A';
            return compact('os', 'arch', 'browser');
        }

        if (stripos($ua, 'bingbot') !== false) {
            $browser = 'Bingbot';
            $os = 'Bot';
            $arch = 'N/A';
            return compact('os', 'arch', 'browser');
        }

        if (stripos($ua, 'Mail.RU_Bot') !== false) {
            $browser = 'Mail.RU Bot';
            $os = 'Bot';
            $arch = 'N/A';
            return compact('os', 'arch', 'browser');
        }

        if (stripos($ua, 'AhrefsBot') !== false) {
            $browser = 'AhrefsBot';
            $os = 'Bot';
            $arch = 'N/A';
            return compact('os', 'arch', 'browser');
        }

        if (stripos($ua, 'Baiduspider') !== false) {
            $browser = 'Baiduspider';
            $os = 'Bot';
            $arch = 'N/A';
            return compact('os', 'arch', 'browser');
        }

        if (preg_match('/Windows NT 10\.0/', $ua)) $os = 'Windows 10';
        elseif (preg_match('/Windows NT 6\.3/', $ua)) $os = 'Windows 8.1';
        elseif (preg_match('/Windows NT 6\.2/', $ua)) $os = 'Windows 8';
        elseif (preg_match('/Windows NT 6\.1/', $ua)) $os = 'Windows 7';
        elseif (preg_match('/Windows NT 6\.0/', $ua)) $os = 'Windows Vista';
        elseif (preg_match('/Windows NT 5\.1/', $ua)) $os = 'Windows XP';
        elseif (stripos($ua, 'Windows') !== false) $os = 'Windows (other)';
        elseif (stripos($ua, 'Mac OS X') !== false) $os = 'macOS';
        elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';
        elseif (stripos($ua, 'Android') !== false) $os = 'Android';
        elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';
        elseif (stripos($ua, 'FreeBSD') !== false) $os = 'FreeBSD';
        elseif (stripos($ua, 'OpenBSD') !== false) $os = 'OpenBSD';

        if (stripos($ua, 'x86_64') !== false || 
            stripos($ua, 'Win64') !== false || 
            stripos($ua, 'x64') !== false || 
            stripos($ua, 'amd64') !== false) {
            $arch = 'x64';
        } elseif (stripos($ua, 'i386') !== false || 
                  stripos($ua, 'i686') !== false || 
                  stripos($ua, 'WOW64') !== false) {
            $arch = 'x86';
        } elseif (stripos($ua, 'arm') !== false) {
            $arch = 'ARM';
        } else {
            $arch = 'N/A';
        }

        if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false && stripos($ua, 'OPR') === false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Edg') !== false || stripos($ua, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($ua, 'Opera') !== false || stripos($ua, 'OPR') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        } elseif (stripos($ua, 'YaBrowser') !== false) {
            $browser = 'Yandex Browser';
        } elseif (stripos($ua, 'Vivaldi') !== false) {
            $browser = 'Vivaldi';
        } elseif (stripos($ua, 'Brave') !== false) {
            $browser = 'Brave';
        } elseif (stripos($ua, 'SamsungBrowser') !== false) {
            $browser = 'Samsung Internet';
        }

        return compact('os', 'arch', 'browser');
    }
}