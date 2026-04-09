<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Resettled Communities</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>

    <!-- Theme CSS -->
    <link href="../css/pinchard.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.js"></script>
    <![endif]-->

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/manifest.json">
    <link rel="mask-icon" href="../favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="../favicon/favicon.ico">
    <meta name="msapplication-config" content="../favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

</head>

<body id="page-top">
    <?php
    require_once __DIR__ . '/../lib/env.php';
    $pinchardGoogleMapsKey = pinchard_env_non_empty('GOOGLE_MAPS_API_KEY');
    ?>
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <a href="../gallery.php" class = "link-to-gallery nav_cloudberry"></a>
        <a class="nav_info" href="info.php"></a>
        <div class = "title">
            <a href="#">Resettled Communities</a>
    </nav>

    <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1-gIU1rTeKAwvGmqoiJZefa8p-qc" width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>


    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.js"></script>

    <!-- Theme JavaScript -->
    <script src="../js/pinchard.js"></script>

    <?php if ($pinchardGoogleMapsKey !== null): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($pinchardGoogleMapsKey, ENT_QUOTES, 'UTF-8') ?>&callback=myMap"></script>
    <?php endif; ?>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G1XKSQNT5M"></script>
    <script>
         window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
         gtag('js', new Date());

         gtag('config', 'G-G1XKSQNT5M');
    </script>

</body>

</html>
