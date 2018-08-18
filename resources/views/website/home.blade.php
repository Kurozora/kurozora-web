<!doctype html>
<html lang="{{ app()->getLocale() }}" style="background-image: url({{ asset('img/static/star_bg_lg.jpg') }});">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Kurozora App</title>

		<!-- jQuery -->
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

		<!-- Stylesheet -->
		<link href="{{ asset('css/kurozora-frontpage.css') }}" rel="stylesheet">
	</head>
	<body>
		<div class="middle-div">
			<img src="{{ asset('img/static/logo_sm.png') }}" id="kurozora-logo" />
			<h1 id="kurozora-title">Kurozora</h1>
		</div>

		<script type="text/javascript">
			$(document).ready(function() {
				$('.middle-div').fadeIn(1000);
			});
		</script>
	</body>
</html>
