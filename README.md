
# Laravel Example: Upload file to AWS S3 Bucket

This is a sample project to demonstrate how we can upload file to S3 Bucket using laravel's storage facade.

Project has one controller **FileController** and two methods **upload()** & **getFile()**. You have to add your AWS credentials in **.env** file for this project to work successfully.

Once project is running in your computer, you can make following cURL request to test.

* **Upload file**

```
curl --location --request POST 'http://localhost:8000/upload' \
--form 'user_file=@"<file_path>"'
```

* **Retrive file**

```
curl --location --request GET '<file_path>'
```

**[Read documentation here...]("https://codershandbook.com/how-to-upload-file-to-s3-bucket-in-laravel")**

