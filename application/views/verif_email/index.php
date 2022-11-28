<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title></title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <style>
      body,html{
        height:100%;
      }
    </style>
  </head>
  <body>
    <div class="container h-100">
      <div class="row h-100 justify-content-center align-items-center">
        <form class="col-12">
          <div class="alert alert-<?php echo $alertClass ?>" role="alert">
            <?php echo $message ?>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
