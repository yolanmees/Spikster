<!DOCTYPE html>
<html>
<head>
    <title>{{ $domain }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
	<center>
		<h4>{{ strtoupper(__('spikster.site')) }}</h4>
		<h1>{{ $domain }}</h1>
    </center>
	<br>
    <h3>SSH/SFTP</h3>
	<ul>
		<li><b>{{ __('spikster.host') }}</b> {{$ip}}</li>
		<li><b>{{ __('spikster.port') }}</b> 22</li>
		<li><b>{{ __('spikster.username') }}</b> {{$username}}</li>
        <li><b>{{ __('spikster.password') }}</b> {{$password}}</li>
        <li><b>{{ __('spikster.path') }}</b> /home/{{ $username }}/web/{{ $path }}</li>
	</ul>
	<br>
	<hr>
	<br>
	<h3>{{ __('spikster.database') }}</h3>
	<ul>
		<li><b>{{ __('spikster.host') }}</b> 127.0.0.1</li>
		<li><b>{{ __('spikster.port') }}</b> 3306</li>
		<li><b>{{ __('spikster.username') }}</b> {{$username}}</li>
		<li><b>{{ __('spikster.password') }}</b> {{$dbpass}}</li>
		<li><b>{{ __('spikster.name') }}</b> {{$username}}</li>
    </ul>
    <br>
	<hr>
    <br>
    <center>
        <p>{!! __('spikster.pdf_site_php_version', ['domain' => $domain, 'php' => $php]) !!}</p>
    </center>
    <br>
	<center>
		<p>{{ __('spikster.pdf_take_care') }}</p>
	</center>
    <br>
    <br>
	<br>
	<center>
		<h5>{{ config('cipi.name') }}<br>({{ config('cipi.website') }})</h5>
	</center>
</body>
</html>
