<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container Styling */
        .container {
            width: 90%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        /* Title Styling */
        h2 {
            color: #4a90e2;
            font-size: 1.8em;
            margin-bottom: 15px;
            font-weight: 700;
        }

        /* Content Styling */
        div {
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
        }

        /* Link Styling */
        a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            color: #fff;
            background-color: #4a90e2;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        a:hover {
            background-color: #357ab7;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>{{ $data['title'] }}</h2>
    <div>
        {{ $data['body'] }}
        @if ($data['redirect'])
            <a href="{{ $data['redirect'] }}">Redirect</a>
            <br>
        @endif
    </div>
</div>

</body>

</html>
