<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Tailsdotcom</title>
        <link rel="stylesheet" href="{{ url('/css/app.css') }}">
        {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">--}}
    </head>
    <body>
        <header>
            <h1>Tails.com</h1>
        </header>
        <div class="wrapper">
            <div id="addresses">
                <ul>
                    @foreach($stores as $store)
                        <li>
                            <h2>{{ $store['name'] }}</h2>
                            <p>{{ $store['postcode'] }}</p>
                            <a href="https://www.google.co.uk/maps/place/{{ $store['postcode'] }}" target="_blank">
                                <img src="{{ $store['map'] }}" alt="street view">
                            </a>
                        </li>
                    @endforeach
                </ul>
                {{ $stores->links() }}
            </div>
        </div>
    </body>
</html>
