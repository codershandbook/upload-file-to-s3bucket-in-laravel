<?php

namespace App\Http\Controllers;

use Aws\S3\Exception\S3Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{

    public function upload(Request $request) {

        if (!$request->hasFile('user_file')) {
            return Response::json([
                'status' => false,
                'message' => 'You have not provided the valid file'
            ]);
        }

        $file = $request->file('user_file');

        // We can get following parameters that can be stored for future use as per your requirement
        $originalFileName = $file->getClientOriginalName();
        $originalFileExtension = $file->getClientOriginalExtension();
        $fileMime = $file->getMimeType();
        $fileContent = $file->getContent(); // File binary data

        try {
            // For S3 Bucket, we need to provide full path, for this example, we will store all user uploaded files
            // in user-files directory and so filename will be as follow.
            $filenameForS3 = 'user-files/' . $originalFileName;

            // You need to install following package for S3 file system to work
            // composer require league/flysystem-aws-s3-v3 "^1.0"
            $response = Storage::disk('s3')->put($filenameForS3, $fileContent, []);

            if ($response) {
                return Response::json([
                    'status' => true,
                    'message' => 'File has been uploaded successfully'
                ]);
            }

            return Response::json([
                'status' => false,
                'message' => 'There was an error while uploading your file'
            ]);
        } catch (S3Exception $ex) {
            return Response::json([
                'status' => false,
                'message' => 'There was an error while uploading your file',
                'error_message' => $ex->getAwsErrorMessage() // We should probably log this instead of providing it in response.
            ]);
        }

    }

    public function getFile(Request $request) {

        if (!$request->has('file_name')) {
            return Response::json([
                'status' => false,
                'message' => 'You have not provided the valid filename'
            ]);
        }

        $originalFileName = $request->get('file_name');

        $filenameForS3 = 'user-files/' . $originalFileName;

        try {
            if (!Storage::disk('s3')->exists($filenameForS3)) {
                return Response::json([
                    'status' => false,
                    'message' => 'File with provided name does not exist',
                ]);
            }

            // If you want to use binary data, then you can use get method
            // $response = Storage::disk('s3')->get($filenameForS3);

            // If you want to get permanent url then you can use url method, For this URL to work, your file should have public access.
            // $response = Storage::disk('s3')->url($filenameForS3);

            // We can use temporaryUrl method to generate signed URL, this is temporary URL that will be unvalidated once it's expired.
            // This URL will work even if your file does not have public access and is secure way to store and share your files.
            $response = Storage::disk('s3')->temporaryUrl($filenameForS3, now()->addMinutes(30));

            if ($response) {
                return Response::json([
                    'status' => true,
                    'message' => 'Your file is retrieved successfully',
                    'file_url' => $response
                ]);
            }

            return Response::json([
                'status' => false,
                'message' => 'There was an error while retrieving your file'
            ]);
        } catch (S3Exception $ex) {
            return Response::json([
                'status' => false,
                'message' => 'There was an error while retrieving your file',
                'error_message' => $ex->getAwsErrorMessage() // We should probably log this instead of providing it in response.
            ]);
        } catch (FileNotFoundException $ex) {
            return Response::json([
                'status' => false,
                'message' => 'File with provided name does not exist',
            ]);
        }

    }

}
