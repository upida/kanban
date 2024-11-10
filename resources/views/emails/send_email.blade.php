<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban</title>
</head>

<body>
<h2>{{ $data['title'] }}</h2>

<div>
    {{ $data['body'] }}

    @if ($data['redirect'])
        <a href="{{ $data['redirect'] }}">Redirect</a>
        <br>
    @endif
</div>

</body>

</html>
