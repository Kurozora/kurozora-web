<?php

namespace App\Helpers;

use CURLFile;

class ResmushIt {
    /**
     * @param string $url
     * @return bool|string
     */
    public static function compress(string $url): bool|string
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $response = self::send($url);
        $imageUrl = json_decode($response)->dest;
        return self::getImg($imageUrl);
    }

    protected static function getImg($url): bool|string
    {
        $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        curl_close($process);
        return $return;
    }

    /**
     * @param string $url
     * @return bool|string
     */
    protected static function send(string $url): bool|string
    {
        logger()->channel('stderr')->info('url: ' . $url);

        $mime = self::getMIME($url);
        $name = basename($url);
        $output = new CURLFile($url, $mime, $name);
        $data = [
            'files' => $output,
        ];

        // Send file to the API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.resmush.it/ws.php?qlty=60');
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $body = curl_exec($ch);

        if (curl_errno($ch)) {
            $body = curl_error($ch);
        }

        curl_close($ch);

        return $body;
    }

    protected static function getImage(string $url): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * Get the mime type of the image.
     *
     * @param string $url
     * @return mixed
     */
    protected static function getMIME(string $url): mixed
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    }
}
