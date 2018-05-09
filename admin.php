<?php
session_start();
include_once('lib/csrf.php');
include_once('lib/auth.php');

//cookie validation
$em=ierg4210_auth();
if($em==false){
	header('Location: login.php', true, 302);
	exit();
}else if($em[1]==0){
	echo 'Permission Denied! Redirect in 3s.';
	//sleep (5);
	header('Refresh: 3; url=index.php');
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Shop - Admin Panel</title>
	<link href="incl/admin.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<h1>IERG4210 Shop - Admin Panel</h1>
<article id="main">


<section id="categoryPanel">
	<fieldset>
		<legend>New Category</legend>
		<form id="cat_insert" method="POST" action="admin-process.php?action=cat_insert" onsubmit="return false;">
			<label for="cat_insert_name">Name</label>
			<div><input id="cat_insert_name" type="text" name="name" required="true" pattern="^[\w\-&' ]+$" /></div>

			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
	<!-- Generate the existing categories here -->
	<ul id="categoryList"></ul>
</section>

<section id="categoryEditPanel" class="hide">
	<fieldset>
		<legend>Editing Category</legend>
		<form id="cat_edit" method="POST" action="admin-process.php?action=cat_edit" onsubmit="return false;">
			<label for="cat_edit_name">Name</label>
			<div><input id="cat_edit_name" type="text" name="name" required="true" pattern="^[\w\-&' ]+$" /></div>
			<input type="hidden" id="cat_edit_catid" name="catid" />
			<input type="submit" value="Submit" /> <input type="button" id="cat_edit_cancel" value="Cancel" />
		</form>
	</fieldset>
</section>

<section id="productPanel">
	<fieldset>
		<legend>New Product</legend>
		<form id="prod_insert" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data">
			<label for="prod_insert_catid">Category *</label>
			<div><select id="prod_insert_catid" name="catid"></select></div>

			<label for="prod_insert_title">Title *</label>
			<div><input id="prod_insert_title" type="text" name="title" required="true" pattern="^[\w\-' ]+$" /></div>

			<label for="prod_insert_name">Name *</label>
			<div><input id="prod_insert_name" type="text" name="name" required="true" pattern="^[\w\-' ]+$" /></div>

			<label for="prod_insert_price">Price *</label>
			<div><input id="prod_insert_price" name="price" required="true" pattern="^[\d]+[\.]?[\d]*$" /></div>

			<label for="prod_insert_description">Description</label>
			<div><textarea id="prod_insert_description" name="description" pattern="^[\w\-,'.! ]*$"></textarea></div>

			<label for="prod_insert_image">Image *</label>
			<div><input type="file" id="prod_insert_image" name="file" required="true" accept="image/jpeg" /></div>

			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
		<!-- Generate the corresponding products here -->
	<ul id="productList"></ul>
</section>
	
<section id="productEditPanel" class="hide">
	<fieldset>
		<legend>Editing Product</legend>
		<form id="prod_edit" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data">
			<label for="prod_edit_catid">Category *</label>
			<div><select id="prod_edit_catid" name="catid"></select></div>

			<label for="prod_edit_title">Title *</label>
			<div><input id="prod_edit_title" type="text" name="title" required="true" pattern="^[\w\-' ]+$" /></div>

			<label for="prod_edit_name">Name *</label>
			<div><input id="prod_edit_name" type="text" name="name" required="true" pattern="^[\w\-' ]+$" /></div>

			<label for="prod_edit_price">Price *</label>
			<div><input id="prod_edit_price" name="price" required="true" pattern="^[\d]+[\.]?[\d]*$" /></div>

			<label for="prod_edit_description">Description</label>
			<div><textarea id="prod_edit_description" name="description" pattern="^[\w\-,'.! ]*$"></textarea></div>

			<label for="prod_edit_image">Change Img</label>
			<div><input type="file" id="prod_edit_image" name="file" accept="image/jpeg" /></div>
			<input type="hidden" id="prod_edit_pid" name="pid" />
			<input type="submit" value="Submit" /><input type="button" id="prod_edit_cancel" value="Cancel" />
		</form>
	</fieldset>
</section>
	



<section id="userPanel">
	<fieldset>
		<legend>New User</legend>
		<form id="user_insert" method="POST" action="admin-process.php?action=user_insert">
			<label for="user_insert_name">Email</label>
			<div><input id="user_insert_name" type="Email" name="name" required="true"/></div>
			<label for="user_insert_password">PW (4-8)</label>
			<div><input id="user_insert_password" type="Password" name="password" required="true" pattern="^.{4,8}$" /></div>
			<label for="user_insert_admin">Admin</label>
			<div><select id="user_insert_admin" name="admin">
  				<option value="0">No</option>
 	 			<option value="1">Yes</option>
			</select></div>

			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
	<!-- Generate the existing categories here -->
	<ul id="userList"></ul>
</section>

<div class="clear"></div>
</article>


<ul id="orderList"></ul>


<script type="text/javascript" src="incl/myLib.js"></script>
<script type="text/javascript">
(function(){

	function updateUI() {
		myLib.get({action:'cat_fetchall'}, function(json){
			// loop over the server response json
			//   the expected format (as shown in Firebug): 
			for (var options = [], listItems = [],
					i = 0, cat; cat = json[i]; i++) {
				options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
				listItems.push('<li id="cat' , parseInt(cat.catid) , '"><span class="name">' , cat.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('prod_insert_catid').innerHTML = '<option></option>' + options.join('');
			el('prod_edit_catid').innerHTML = '<option></option>' + options.join('');
			el('categoryList').innerHTML = listItems.join('');
		});
		el('productList').innerHTML = '';

		myLib.post({action:'user_fetchall'}, function(json){
			for (var options = [], listItems = [],
				i = 0, user; user = json[i]; i++) {
				if(user.admin==1)
					listItems.push('<li id="user' , parseInt(user.userid) , '"><span class="name">' , user.email.escapeHTML()+'(admin)' , '</span> <span class="delete">[Delete]</span> </li>');
				else
					listItems.push('<li id="user' , parseInt(user.userid) , '"><span class="name">' , user.email.escapeHTML()+'(user)' , '</span> <span class="delete">[Delete]</span> </li>');
			}
			el('userList').innerHTML = listItems.join('');
			});

		myLib.post({action:'order_fetchall'}, function(json){
			for (var options = [], listItems = [],
				i = 0, order; order = json[i]; i++) {
				if(order.tid)
					listItems.push('<li id="oid' , parseInt(order.oid) , '"><span class="name"> <b>OID</b>:' , order.oid.escapeHTML()+' <b>Digest</b>:' , order.digest.escapeHTML()+' <b>Salt</b>:', order.salt.escapeHTML()+' <b>Date</b>:', order.createdtime.escapeHTML()+' <b>TID</b>:', order.tid.escapeHTML()+'</span> <span class="delete">[Delete]</span> </li>');
				else
					listItems.push('<li id="oid' , parseInt(order.oid) , '"><span class="name"> <b>OID</b>:' , order.oid.escapeHTML()+' <b>Digest</b>:' , order.digest.escapeHTML()+' <b>Salt</b>:', order.salt.escapeHTML()+' <b>Date</b>:', order.createdtime.escapeHTML()+' <b>TID</b>:Not paid</span> <span class="delete">[Delete]</span> </li>');
			}
			el('orderList').innerHTML = listItems.join('');
			});
	}
	updateUI();
	
	el('categoryList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^cat/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'cat_delete', catid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
			});
		
		// handle the edit click
		} else if ('edit' === target.className) {
			// toggle the edit/view display
			el('categoryEditPanel').show();
			el('categoryPanel').hide();
			
			// fill in the editing form with existing values
			el('cat_edit_name').value = name;
			el('cat_edit_catid').value = id;
		
		//handle the click on the category name
		} else {
			myLib.get({catid:id}, function(json){
				for (var options = [], listItems = [],
					i = 0, product; product = json[i]; i++) {
				listItems.push('<li id="prod' , parseInt(product.pid) , '"><span class="name">' , product.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('productList').innerHTML = listItems.join('');
			});
			el('prod_insert_catid').value = id;
			// populate the product list or navigate to admin.php?catid=<id>
			//el('productList').innerHTML = '<li> Product 1 of "' + name + '" [Edit] [Delete]</li><li> Product 2 of "' + name + '" [Edit] [Delete]</li>';
		}
	}
	
	el('userList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^user/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'user_delete', userid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
			});
		} 
	}

	el('productList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^prod/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'prod_delete', pid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
			});
		
			// handle the edit click
		} else if ('edit' === target.className) {
			// toggle the edit/view display
			el('productEditPanel').show();
			el('productPanel').hide();
			
			// fill in the editing form with existing values
			el('prod_edit_pid').value = id;
			myLib.get({pid:id}, function(json){
				el('prod_edit_catid').value = json[0].catid;
				el('prod_edit_title').value = json[0].title;
				el('prod_edit_name').value = json[0].name;
				el('prod_edit_description').value = json[0].description;
				el('prod_edit_price').value = json[0].price;
			});
		}
	}

	el('orderList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^oid/, '');
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'order_delete', oid: id}, function(json){
				alert('Transaction is deleted successfully!');
				updateUI();
			});
		} 
	}
	
	el('cat_insert').onsubmit = function() {
		return myLib.submit(this, function(){
			el('cat_insert_name').value = "";
			updateUI();
		});
	}
	el('cat_edit').onsubmit = function() {
		return myLib.submit(this, function() {
			// toggle the edit/view display
			el('categoryEditPanel').hide();
			el('categoryPanel').show();
			updateUI();
		});
	}
	el('cat_edit_cancel').onclick = function() {
		// toggle the edit/view display
		el('categoryEditPanel').hide();
		el('categoryPanel').show();
	}
	el('prod_edit_cancel').onclick = function() {
		// toggle the edit/view display
		el('productEditPanel').hide();
		el('productPanel').show();
		el('prod_edit_catid').value = "";
		el('prod_edit_title').value = "";
		el('prod_edit_name').value = "";
		el('prod_edit_description').value = "";
		el('prod_edit_price').value = "";
	}

})();
</script>
</body>
</html>
