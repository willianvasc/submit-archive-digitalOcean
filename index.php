<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function uploadAndGeneratePresignedUrl($bucketName, $filePath, $keyName, $expiration) {

    $accessKeyId = 'access';
    $secretAccessKey = 'secretAccessKey';
    $region = 'region';
    $endpoint = 'digital_ocean_link';

    // Criando um cliente S3 compatível com DigitalOcean Spaces
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => $region,
        'endpoint' => $endpoint,
        'credentials' => [
            'key'    => $accessKeyId,
            'secret' => $secretAccessKey,
        ]
    ]);

    try {
        // Fazendo o upload do arquivo
        $result = $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => $keyName,
            'SourceFile' => $filePath,
            'ACL'    => 'private',
        ]);

        // Gera a URL pré-assinada
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key'    => $keyName
        ]);

        // Definindo o tempo de expiração da URL
        $request = $s3Client->createPresignedRequest($cmd, '+' . $expiration . ' minutes');

        // Retorna a URL pré-assinada
        return (string)$request->getUri();

    } catch (AwsException $e) {
        echo "Erro: " . $e->getMessage();
        return false;
    }
}

// File path
$bucketName = 'name-bucket';
$filePath = 'filename';
$keyName = 'path';
$expiration = 60; // time in minutes

$presignedUrl = uploadAndGeneratePresignedUrl($bucketName, $filePath, $keyName, $expiration);
if ($presignedUrl) {
    echo "Arquivo enviado com sucesso! URL pré-assinada: " . $presignedUrl;
} else {
    echo "Falha no envio.";
}

