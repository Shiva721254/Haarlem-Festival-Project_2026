<!DOCTYPE html>
<html>
<body>

<h1>The XMLHttpRequest Object</h1>
<div> 
    <p id="demo">Let AJAX change this text.</p>
</div>
<button type="button" onclick="loadDoc()">Change Content</button>

<script>
function loadDoc() {
    var xhttp = new XMLHttpRequest();

    xhttp.responseType = "blob";

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var imgUrl = URL.createObjectURL(this.response);

        document.getElementById("demo").innerHTML = '<img src="' + imgUrl + '" />';
        }
    };
    xhttp.open("GET", "/assets/images/hacker.jpg", true);
    xhttp.send();
}
</script>

</body>
</html>

<?php
// Array with names
$a[] = "Anna";
$a[] = "Brittany";
$a[] = "Cinderella";
$a[] = "Diana";
$a[] = "Eva";
$a[] = "Fiona";
$a[] = "Gunda";
$a[] = "Hege";
$a[] = "Inga";
$a[] = "Johanna";
$a[] = "Kitty";
$a[] = "Linda";
$a[] = "Nina";
$a[] = "Ophelia";
$a[] = "Petunia";
$a[] = "Amanda";
$a[] = "Raquel";
$a[] = "Cindy";
$a[] = "Doris";
$a[] = "Eve";
$a[] = "Evita";
$a[] = "Sunniva";
$a[] = "Tove";
$a[] = "Unni";
$a[] = "Violet";
$a[] = "Liza";
$a[] = "Elizabeth";
$a[] = "Ellen";
$a[] = "Wenche";
$a[] = "Vicky";

// get the q parameter from URL
$q = $_REQUEST["q"];

$hint = "";

// lookup all hints from array if $q is different from ""
if ($q !== "") {
    $q = strtolower($q); // all to lower
    $len=strlen($q); // check the length 
    foreach($a as $name) {
        if (stristr($q, substr($name, 0, $len))) {
            if ($hint === "") {
                $hint = $name;
            } else {
                $hint .= ", $name";
            }
        }
    }
}

// Output "no suggestion" if no hint was found or output correct values
echo $hint === "" ? "no suggestion" : $hint;
?>