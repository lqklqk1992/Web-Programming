<?php
session_start();
include_once('lib/auth.php');

//cookie validation
$em=ierg4210_auth();
if($em==false){
	$entry='<a id="login_entry" href="login.php">Hi, please login!</a>';
}
else
	$entry='<a id="login_entry" href="auth-process.php?action=logout">'.$em[0].'</a><span id="user_history">[History]</span><a id="change_pw" href="change-password.php">[Change password]<a>';
?>
<!DOCTYPE html>
<html lang="en"> 

<head>
	<meta charset="utf-8" />

	<style>
		body{background:#eee;}
		nav.categorylist{left:50px;top:0px;width: 350px;height:100%;background:orange;-webkit-user-select:none;z-index:-1;}
		#footer_word{position:fixed;text-align: center;font-size: smaller; bottom:0;left:45%;font-family:Georgia, Serif;}
		#header_word{position:fixed;text-align: center;font-size: 150%; top:30px;left:35%;font-family:Georgia, Serif;}
		#login_entry{position:absolute;left:140px;top:100px;color:#eee;font-size: 120%;}
		#user_history{position:absolute;left:140px;top:125px;color:#eee;font-size: 110%;}
		#change_pw{position:absolute;left:140px;top:145px;color:#eee;font-size: 110%;}
		div.ShoppingList h3{position:absolute;right:15%; border: solid 10px #663300;background:#663300;color:#eee;}
		div.ShoppingList h3 ul{display:none}
		div.ShoppingList h3:hover ul{display:block}
		div.ShoppingList h3 p{font-size: 80%;}
		input.shopping_product_quantity{width: 40px}
		nav{position:absolute;left:450px;top:100px;white-space:nowrap}
		ul.categorytable{position:absolute;left:50px;top:200px;cursor:pointer;text-decoration:underline;-webkit-user-select:none;color:white;font-size: 120%;}
		ul.producttable{position:absolute;left:500px;top:200px;font-family:Georgia, Serif;width:840px;height:440px;margin:0;padding:0;list-style:none;overflow:auto;z-index:-1;}
		ul.producttable li{position:relative;width:200px;height:250px;float:left;border:1px solid #CCC;color:#000;font-size:100%;text-align: center;background:white;}
		ul.producttable li strong{position:absolute;left:110px;top:30px}
		ul.producttable li b{position:absolute;top:110px;left:0px;float:left}
		ul.producttable li p.price{position:absolute;left:60px;top:40px;color:#F00;font-size:100%}
		#product_detail{position:absolute;left:30%;top:20%;width:50%;height:50%;background: white;z-index:-1;}
		#product_detail img{float:left;}
		div.detail_text{position:relative;top:20%;font-family:Georgia, Serif;text-align: center}
		div.detail_text p.price{color:#F00;font-size:100%}
		.navlink{cursor:pointer;text-decoration:underline;-webkit-user-select:none}
		#user_history{cursor:pointer;text-decoration:underline;-webkit-user-select:none}
		#purchase_detail{position:absolute;left:30%;top:20%;width:50%;height:50%;z-index:-1;}
		#purchase_detail li strong{position:relative;top:20%;font-family:Georgia, Serif;text-align: left}
		.hidden { display : none }
		.display_area { display : block }
	</style>

	<script src="jquery-3.1.1.min.js"></script>
	<script src="jquery.form.js"></script>
	<title>VerySimple Supermarket</title>
</head>

<body>
<?php echo $entry;?>
<div class="ShoppingList">
	<h3>Shopping list <span id="totalcost">$0</span>		
		<form id="shopping_form" method="POST" action="https://www.sandbox.paypal.com/cgi-bin/webscr">
			<ul id="shopping_list"></ul>
			<input type="hidden" name="cmd" value="_cart" />
			<input type="hidden" name="upload" value="1" />
			<input type="hidden" name="charset" value="utf-8" />
			<input type="hidden" name="custom" value="0" />
			<input type="hidden" name="invoice" value="0" />
			<input id="btncheckout" type="submit" value="CheckOut" />
		</form>
	</h3>
</div>


<div class="Nav">
	<nav>
		<span id="layer1">
			<a href="index.php">Home</a>
		</span>
		<span id="layer2" class="hidden">
			> 
			<b id="layer2_text">Breakfast & Bakery</b>
		</span>
		<span id="layer3" class="hidden">
			>
			<b id="layer3_text">Viva Milk</b>
		</span>
	</nav>
</div>


<div class="CategoryList">
	<nav class="categorylist">
	<ul class="categorytable"></ul>
	</nav>
</div>


<div class="ProductList">

	<div id="product_table" class="hidden">
		<ul class="producttable"></ul>
	</div>

	<div id="product_detail" class="hidden"></div>

</div>

<div id="purchase_detail" class="hidden"></div>

<header>
	<b id="header_word">Welcome to VerySimple Supermarket</b>
</header>
<footer>
	<p id="footer_word">VerySimple Online</p>
</footer>
</body>


<script type="text/javascript">

var logged = <?php echo(json_encode($em)); ?>;

$('form').submit(function() {
	//check if logged in, if not then ask user if he wants to check out without login
	if(logged==false){
		var r = confirm("CheckOut as a guest?");
		if (r == false) {
    		location.replace("login.php");
    		return false;
		} 
	}
	

	var data = {};
	var myNode = document.getElementById('shopping_list');
	if(!myNode.hasChildNodes()){return false;}
	else{
		var children = myNode.childNodes;
		for (var i = 0; i < children.length; i++) {
			var shoppingform = document.getElementById('shopping_form');
			var quantity = children[i].getElementsByClassName("shopping_product_quantity")[0];
			var newInput = document.createElement("input");

			newInput.type="hidden";
			newInput.name="item_name_"+(i+1);
			newInput.value=children[i].firstChild.innerHTML;
			shoppingform.appendChild(newInput);

			newInput = document.createElement("input");
			newInput.type="hidden";
			newInput.name="item_number_"+(i+1);
			newInput.value=quantity.id;
			shoppingform.appendChild(newInput);

			newInput = document.createElement("input");
			newInput.type="hidden";
			newInput.name="quantity_"+(i+1);
			newInput.value=quantity.value;
			shoppingform.appendChild(newInput);

			data[(i+1)]={"pid":quantity.id,"quantity":quantity.value};
 		}
 		if(logged!=false)
 			data['user']=logged[0];		
	}


    $.ajax({type: 'POST', url:'checkout-process.php', async: false, data:data, success:function(output){
//remove "while(1);"
    	if(output.substr(0,9) == 'while(1);'){ output=output.substring(9);}
// to decode the xhr.responseText and turns it to an object
		var json = JSON.parse(output);
		if (json.success) {
			var invoice=json.success["invoice"];
			var custom=json.success["custom"];
			var price=json.success["price"];
			var input=document.getElementsByName('invoice')[0];
			input.value=invoice;
			input=document.getElementsByName('custom')[0];
			input.value=custom;
			for(var i = 1,record;record = price[i]; i++){
				var shoppingform = document.getElementById('shopping_form');
				newInput = document.createElement("input");
				newInput.type="hidden";
				newInput.name="amount_"+i;
				newInput.value=record;
				shoppingform.appendChild(newInput);
			}
			newInput = document.createElement("input");
			newInput.type="hidden";
			newInput.name="business";
			newInput.value="471794027-facilitator@qq.com";
			shoppingform.appendChild(newInput);
			newInput = document.createElement("input");
			newInput.type="hidden";
			newInput.name="currency_code";
			newInput.value="HKD";
			shoppingform.appendChild(newInput);
		} else alert('Error!');
	}});
    localStorage.clear();
    return true; // return false to cancel form action
});

function updateShoppinglist(){
//delete old cat list
	var myNode = document.getElementById('shopping_list');
	while (myNode.firstChild&&myNode.firstChild.tagName=='LI') {
    	myNode.removeChild(myNode.firstChild);
    }

	var cartContent = localStorage.getItem("ierg4210_cart");
	if (cartContent) {
        cartContent = JSON.parse(cartContent);
    } else {
        cartContent = {};
    }
    var totalcost=0;
    for(var key in cartContent){
    	var li = document.createElement("li");

    	var name = document.createElement("p");
    	name.innerHTML=cartContent[key]['title'];
    	name.className="shopping_product_name";
    	li.appendChild(name);

    	var num = document.createElement("div");
    	num.className="shopping_product_num";
    	var numInput = document.createElement("input");
    	numInput.className="shopping_product_quantity";
    	numInput.id=key;
    	numInput.type="number";
    	numInput.min=0;
    	numInput.value=cartContent[key]['num'];
    	num.appendChild(numInput);

    	var increaseBtn = document.createElement("input");
    	increaseBtn.type="button";
    	increaseBtn.value = "+";
   	 	num.appendChild(increaseBtn);

    	var decreaseBtn = document.createElement("input");
    	decreaseBtn.type="button";
    	decreaseBtn.value = "-";
    	num.appendChild(decreaseBtn);

    	li.appendChild(num);

    	var cost = document.createElement("p");
    	cost.className="shopping_product_cost";
    	cost.innerHTML = Number(cartContent[key]['num'] * cartContent[key]['price']).toFixed(2) + "HK$";
    	li.appendChild(cost);

    	totalcost=totalcost+Number(cartContent[key]['num'] * cartContent[key]['price']);
    	var list=document.getElementById("shopping_list");
    	list.insertBefore(li, list.childNodes[0]);
    	

        increaseBtn.onclick = function () {
        	var a=this.parentElement
        	a.firstChild.value = parseInt(a.firstChild.value) + 1;
            a.firstChild.onchange();
        }

        decreaseBtn.onclick = function () {
        	var a=this.parentElement
            a.firstChild.value = parseInt(a.firstChild.value) - 1;
            a.firstChild.onchange();
        }

        numInput.onchange = function () {
            var currentQuantity = parseInt(this.value);
            var cartContent = localStorage.getItem("ierg4210_cart");
            if (cartContent) {
        		cartContent = JSON.parse(cartContent);
    		} else {
        		cartContent = {};
    		}
    		if(currentQuantity==0){
    			delete cartContent[this.id];
    		}else{
    			cartContent[this.id]['num']=currentQuantity;
    		}
           	localStorage.setItem("ierg4210_cart", JSON.stringify(cartContent));
           	updateShoppinglist();
        }    	
    }
    document.getElementById("totalcost").innerHTML='$'+ totalcost.toFixed(2);
}


function addtocartClick(e){
	var a=e.target;

	$.ajax({url:'public-process.php?'+a.id,success:function(output){

//remove "while(1);"
    	if(output.substr(0,9) == 'while(1);'){ output=output.substring(9);}
// to decode the xhr.responseText and turns it to an object
		var json = JSON.parse(output);
		if (json.success) {
			record=json.success[0];
			var cartContent = localStorage.getItem("ierg4210_cart");
    		if (cartContent) {
        		cartContent = JSON.parse(cartContent);
    		} else {
        		cartContent = {};
    		}
    		if(cartContent[record.pid]){
    			cartContent[record.pid]['num']=cartContent[record.pid]['num']+1;
    		}else{
    			cartContent[record.pid]={};
    			cartContent[record.pid]['title']=record.title;
    			cartContent[record.pid]['num']=1;
    			cartContent[record.pid]['price']=record.price;
    		}
    		localStorage.setItem("ierg4210_cart", JSON.stringify(cartContent));
    		updateShoppinglist()
		} else alert('Error!');
	}});
	
}

function updateCategories() {

	$.ajax({url:'public-process.php',success:function(output){
//delete old cat list
		var myNode = document.getElementsByClassName('categorytable')[0];
		while (myNode.firstChild) {
    		myNode.removeChild(myNode.firstChild);
    	}
//remove "while(1);"
    	if(output.substr(0,9) == 'while(1);'){ output=output.substring(9);}
// to decode the xhr.responseText and turns it to an object
		var json = JSON.parse(output);
		if (json.success) {
// to print out each record with proper output sanitizations
			for (var i = 0, record; record = json.success[i]; i++) {
				var num = (i+1).toString();
				
//create new li element
				var newli = document.createElement("li"); 
				newli.id = 'catid='+num;
 				var newContent = document.createTextNode(record.name); 
  				newli.appendChild(newContent); //add the text node to the newly created div. 

// add the newly created element and its content into the DOM 
  				var Catlist = document.getElementsByClassName('categorytable')[0];
  				Catlist.appendChild(newli);
			}

//add mouse listener to change color
			$("ul.categorytable li").mousedown(function(){
				this.style.color="#000";
			});

			$("ul.categorytable li").mouseup(function(){
				this.style.color="#FFF";
			});
		} else alert('Error!');
	}});

}


function updateProductList(param) {

	$.ajax({url:'public-process.php?'+param,success:function(output){
//delete all product list
		var myNode = document.getElementsByClassName('producttable')[0];
		while (myNode.firstChild) {
    		myNode.removeChild(myNode.firstChild);
    	}
//delete all product detail
    	myNode = document.getElementById('product_detail');
		while (myNode.firstChild) {
    		myNode.removeChild(myNode.firstChild);
    	}
//remove "while(1);"
    	if(output.substr(0,9) == 'while(1);'){ output=output.substring(9);}
// to decode the xhr.responseText and turns it to an object
		var json = JSON.parse(output);
		if (json.success) {
// to print out each record with proper output sanitizations
			for (var i = 0, record; record = json.success[i]; i++) {
				var num = (i+1).toString();
				
				//create new li element
				var newli = document.createElement("li"); 
				newli.id = param+'&title='+record.title;
				newli.addEventListener("click",GoToDetail, false);
				var newimg = document.createElement("img");
				newimg.src = "incl/img/"+record.pid+".jpg";
				newimg.height = "100";
				newimg.width = "100";
				newimg.align = "left";
				newimg.alt = record.title;
				newimg.title = record.title;
				newli.appendChild(newimg);

				var newstrong = document.createElement("strong"); 
				var newContent = document.createTextNode(record.title); 
				newstrong.appendChild(newContent);
				newli.appendChild(newstrong);

				var newp = document.createElement("p"); 
				var newb = document.createElement("b");
				newContent = document.createTextNode(record.name); 
				newb.appendChild(newContent);
				newp.appendChild(newb);
				newli.appendChild(newp);

				newp = document.createElement("p");
				newp.className="price";
				newb = document.createElement("b");
				newContent = document.createTextNode("HK$"+record.price); 
				newb.appendChild(newContent);
				newp.appendChild(newb);
				newli.appendChild(newp);

  				// add the newly created element and its content into the DOM 
  				var Catlist = document.getElementsByClassName('producttable')[0];
  				Catlist.appendChild(newli);
			}
			$("#product_table").addClass("display_area");
		} else alert('Error!');
	}});
}


function updateProductDetail(param) {

$.ajax({url:'public-process.php?'+param,success:function(output){
// to decode the xhr.responseText and turns it to an object
//remove "while(1);"
    	if(output.substr(0,9) == 'while(1);'){ output=output.substring(9);}
		var json = JSON.parse(output);
		if (json.success) {

			record=json.success[0];
			var ProductDetail = document.getElementById('product_detail');
  				var newimg = document.createElement("img");
				newimg.src = "incl/img/"+record.pid+".jpg";
				newimg.height = "300";
				newimg.width = "300";
				newimg.alt = record.title;
				newimg.title = record.title;
				ProductDetail.appendChild(newimg);

				var newdiv = document.createElement("div");
				newdiv.className="detail_text";
				var newstrong = document.createElement("strong"); 
				var newContent = document.createTextNode(record.name); 
				newstrong.appendChild(newContent);
				newdiv.appendChild(newstrong);

				var newp = document.createElement("p"); 
				newContent = document.createTextNode(record.description); 
				newp.appendChild(newContent);
				newdiv.appendChild(newp);

				newp = document.createElement("p");
				newp.className="price";
				newContent = document.createTextNode("HK$"+record.price); 
				newp.appendChild(newContent);
				newdiv.appendChild(newp);

				newbutton = document.createElement("button");
				newContent = document.createTextNode("[Add to shopping cart]");
				newbutton.id='pid='+record.pid;
				newbutton.appendChild(newContent);
				newdiv.appendChild(newbutton);
				newbutton.addEventListener("click",addtocartClick, false);

				ProductDetail.appendChild(newdiv);

		} else alert('Error!');
	}});

}

function updateUserHistory() {
	if(logged==false)
		return false;
	$.ajax({url:'public-process.php?em='+logged[0],success:function(output){
//delete old UserHistory
		var myNode = document.getElementById('purchase_detail');
		while (myNode.firstChild) {
    		myNode.removeChild(myNode.firstChild);
    	}
//remove "while(1);"
    	if(output.substr(0,9) == 'while(1);'){ output=output.substring(9);}
// to decode the xhr.responseText and turns it to an object
		var json = JSON.parse(output);
		if (json.success) {
// to print out each record with proper output sanitizations
			for (var i = 0, record; record = json.success[i]; i++) {
				var num = (i+1).toString();
				
				if(i==0){
					var newstrong = document.createElement("strong"); 
 					var newContent = document.createTextNode("Purchase History:"); 
  					newstrong.appendChild(newContent);	
  					myNode.appendChild(newstrong);
				}

//create new li element
				var newli = document.createElement("li"); 
 				var newContent = document.createTextNode(num+": "+record.data); 
  				newli.appendChild(newContent); //add the text node to the newly created div. 

// add the newly created element and its content into the DOM 
  				myNode.appendChild(newli);
			}

		} else alert('Error!');
	}});
}



	updateCategories();
	updateShoppinglist();
	updateProductList("catid=1");
	


	$("#layer2").addClass("display_area");

	function turnred(e){e.target.style.color="#F00";}
	function turnblack(e){e.target.style.color="#000";}

	function ClickLayer2Nav(e){
		$(".display_area").removeClass("display_area");
		$(".navlink").removeClass("navlink");
		$("#layer2_text")[0].removeEventListener("mousedown",turnred, false);
		$("#layer2_text")[0].removeEventListener("mouseup",turnblack, false);
		$("#layer2_text")[0].removeEventListener("click",ClickLayer2Nav, false);
		$("#layer2").addClass("display_area");

//delete all product detail
		var myNode = document.getElementById('product_detail');
		while (myNode.firstChild) {
    		myNode.removeChild(myNode.firstChild);
    	}
		$("#product_table").addClass("display_area");
	}

	function GoToDetail(e){
		$(".display_area").removeClass("display_area");

		var a=e.target;
		while(a.id.indexOf("&title=")==-1){a=a.parentElement;}
		updateProductDetail(a.id);
		updateShoppinglist();
		
		document.getElementById("layer3_text").innerHTML=a.id.substring(a.id.indexOf("title=")+6);

		$("#product_detail").addClass("display_area");
		$("#layer2_text").addClass("navlink");
		$("#layer2").addClass("display_area");
		$("#layer3").addClass("display_area");
		$("#layer2_text")[0].addEventListener("mousedown",turnred, false);
		$("#layer2_text")[0].addEventListener("mouseup",turnblack, false);
		$("#layer2_text")[0].addEventListener("click",ClickLayer2Nav, false);
		
		
	}

	function SwitchCategory(e){
		$(".display_area").removeClass("display_area");
		$(".navlink").removeClass("navlink");
		$("#layer2_text")[0].removeEventListener("mousedown",turnred, false);
		$("#layer2_text")[0].removeEventListener("mouseup",turnblack, false);
		$("#layer2_text")[0].removeEventListener("click",ClickLayer2Nav, false);
		$("#layer2").addClass("display_area");

		updateCategories();
		updateShoppinglist();

		var a=e.target;
		while(!a.id.startsWith("catid=")){a=a.parentElement;}
		updateProductList(a.id);


		document.getElementById("layer2_text").innerHTML=a.innerHTML;
		$("#layer2").addClass("display_area");
	}

	function GoToUserHistory(e){
		$(".display_area").removeClass("display_area");
		$(".navlink").removeClass("navlink");
		$("#layer2_text")[0].removeEventListener("mousedown",turnred, false);
		$("#layer2_text")[0].removeEventListener("mouseup",turnblack, false);
		$("#layer2_text")[0].removeEventListener("click",ClickLayer2Nav, false);

		updateUserHistory();
		$("#purchase_detail").addClass("display_area");
		document.getElementById("layer2_text").innerHTML=logged[0];
		$("#layer2").addClass("display_area");
	}

	if(logged!=false){
		$("#user_history").mousedown(function(){this.style.color="#000";});
		$("#user_history").mouseup(function(){this.style.color="#FFF";});
		$("#user_history")[0].addEventListener("click",GoToUserHistory, false);
	}
	
	$("ul.categorytable")[0].addEventListener("click",SwitchCategory, false);


</script>