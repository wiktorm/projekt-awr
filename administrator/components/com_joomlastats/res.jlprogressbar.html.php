<html>
<head>
<style type="text/css">
#ProgressBar #progressDone {
    background-color: #fd6704;
    border: thin solid #ddd;
}

#ProgressBar #progressToGo {
    background-color: #eee;
    border: thin solid #ddd;
}
</style>
<script type="text/javascript">
	function updateProgressBar(title, description, percentComplete, timeRemaining, memoryInfo) {
		document.getElementById('progressTitle').innerHTML = title;
		document.getElementById('progressDescription').innerHTML = description;
			
		var progressMade = Math.round(percentComplete * 100);
		var progressToGo = document.getElementById('progressToGo');
			
		if (progressMade == 100) {
			 progressToGo.style.display = 'none'; 
		} else {
			 progressToGo.style.display = 'inline-block';
			 progressToGo.style.width = (100 - progressMade) + "%";
		}
			
		document.getElementById('progressDone').style.width = progressMade + "%";
		document.getElementById('progressTimeRemaining').innerHTML = timeRemaining;
		document.getElementById('progressMemoryInfo').innerHTML = memoryInfo;
	}
			
	function completeProgressBar(url) {
		var link = document.getElementById('progressContinue');
		link.href = url;
		link.style.display = 'block';
	}
			
	function errorProgressBar(html) {
		var errorInfo = document.getElementById('progressErrorInfo');
		errorInfo.innerHTML = html;
		errorInfo.style.display = 'block';
	}
</script>
</head>
<body>
<div id="progressBar" class="block">
	<h3 id="progressTitle">
		&nbsp;
	</h3>
	
	<p id="progressDescription">
		&nbsp;
	</p>
	
	<table width="80%" cellspacing="0" cellpadding="0">
		<tr>
			<td id="progressDone" style="display: inline-block; width:0%">&nbsp;</td>
		    <td id="progressToGo" style="display: inline-block; width:100%; border-left: none">&nbsp;</td>
		</tr>
	</table>
		
	<p id="progressTimeRemaining">
		&nbsp;
	</p>
		
	<p id="progressErrorInfo" style="display: none">
	</p>
	
	<p id="progressMemoryInfo" style="position: absolute; top: 0px; right: 15px">
    &nbsp;
  	</p>
		
	<a id="progressContinue" style="display: none">
		Continue....
	</a>
</div>
</body>
</html>