<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="keywords" content="math,science" />
		<link rel="stylesheet" type="text/css" href="demo_style.css" />
		<script type="text/javascript" src="./ckeditor/plugins/ckeditor_wiris/core/display.js"></script>
		<!-- <script type="text/javascript" src="./ckeditor4/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image"></script> -->
		<script type="text/javascript" src="./ckeditor/ckeditor.js"></script>
		<title>CKEditor 4 WIRIS integration on PHP | Educational mathematics</title>

		<script type="text/javascript">
			// Retrieve language from either URL or POST parameters
			function getParameter(name,dflt) {
				var value = new RegExp(name+"=([^&]*)","i").exec(window.location);
				if (value!=null && value.length>1) return decodeURIComponent(value[1].replace(/\+/g,' ')); else return dflt;
			}
		    var lang = getParameter("language","<?php $temp = &$_POST["language"]; echo isset($temp)?$temp:""; ?>");
			if (lang.length==0) lang="en";
		</script>

		<style>
			#iconsDiv {display:inline-block;}
			#langFormDiv { display:inline-block; margin-left:640px;}
		</style>
		<!--[if IE]><style>#langFormDiv { margin-left:640px; }</style><![endif]-->
		<!--[if lt IE 9]><style>#langFormDiv { margin-left:645px; }</style><![endif]-->
		<!--[if lt IE 8]><style>#iconsDiv {display:inline;zoom:1; margin-bottom:-20px;} #langFormDiv { display:inline; zoom:1; margin-bottom:-20px;}</style><![endif]-->

	</head>

	<body>


		<form id="form" name="langForm" method="POST">
			<h2>New icons in the editor</h2>


			<div id="exampleContent" style="display: none">
				<h1>WIRIS works in <em>inline</em> mode</h1>

				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

				<p style="text-align: center;"><math xmlns="http://www.w3.org/1998/Math/MathML"><mi>x</mi><mo>=</mo><mfrac><mrow><mo>-</mo><mi>b</mi><mo>&plusmn;</mo><msqrt><msup><mi>b</mi><mn>2</mn></msup><mo>-</mo><mn>4</mn><mi>a</mi><mi>c</mi></msqrt></mrow><mrow><mn>2</mn><mi>a</mi></mrow></mfrac></math></p>

				<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur <math xmlns="http://www.w3.org/1998/Math/MathML"><msup><mi>x</mi><mn>2</mn></msup><mo>+</mo><mn>5</mn><mi>x</mi><mo>-</mo><mn>2</mn><mo>=</mo><mn>0</mn></math> sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</p>
			</div>

			<div id="example" style="border: 1px solid #666; width: 850px; padding: 10px;" contenteditable="true">
				<?php $temp = &$_POST["content"]; echo isset($temp)?$temp:""; ?>
			</div>


			<input id="hiddenContent" type="hidden" name="content" />
			<script type="text/javascript">
				var exampleContainer = document.getElementById('example');

				if (exampleContainer.innerHTML.trim().length == 0) {
					exampleContainer.innerHTML = document.getElementById('exampleContent').innerHTML;
				}

				CKEDITOR.config.toolbar_Full =
				[
					{ name: 'document', items : [ 'Source'] },
					{ name: 'clipboard', items : [ 'Cut','Copy','Paste','-','Undo','Redo' ] },
					{ name: 'editing', items : [ 'Find'] },
					{ name: 'basicstyles', items : [ 'Bold','Italic','Underline'] },
					{ name: 'paragraph', items : [ 'JustifyLeft','JustifyCenter','JustifyRight'] }
				];

				CKEDITOR.inline('example', {
					language: lang,
					toolbar:'Full'
					//wirisimagecolor:'#000000',
					//wirisbackgroundcolor:'#ffffff',
					//wirisimagefontsize:'16px'
				});

				form.onsubmit = function () {
					document.getElementById('hiddenContent').value = CKEDITOR.instances.example.getData();
				}
			</script>
		</form>



	</body>
</html>
