<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Tailor Stitch</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <style>
        body {
            /* background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); */
            background-image: url('https://media.istockphoto.com/id/1455584807/photo/mannequin-with-tailored-suit-in-luxury-atelier.jpg?s=612x612&w=0&k=20&c=6eDYWZZrgh-LPmhKC3eDe-IzQciur_6iwMJni9VX8K8='); 
            background-size: cover;
            background-position: center;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        h1 {
            font-size: 3em;
            margin: 0;
            animation: fadeIn 3s forwards;
        }
        #animated-word {
            color:rgb(6, 233, 25);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <h1> Welcome to Tailor <span id="animated-word">Stitch</span></h1>
</body>
</html>
