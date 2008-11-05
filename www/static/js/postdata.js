/**
 * DHTML for posting data
 * (c) 2008 infoarena
 */

function PostData(url, data) {
	var myForm = document.createElement("form");
	myForm.method = "post";
	myForm.action = url;
	for (var k in data) {
		var myInput = document.createElement("input") ;
		myInput.setAttribute("name", k);
		myInput.setAttribute("value", data[k]);
		myForm.appendChild(myInput);
	}

	document.body.appendChild(myForm);
	myForm.submit();
	document.body.removeChild(myForm);
}
