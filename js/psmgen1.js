/**
  * Module Custom Best Sellers (v1.0)
  * 
  * For Help & Support,
  * selectomer TIC <info@selectomer.com>
  * http://www.selectomer.com
  */

/* Global Variables */
zoneCurrent = 0;
selectionCurrent = null;
valueOfZoneEdited = null;

// Last item is used to save the current zone and 
// allow to replace it if user cancel the editing
lastEditedItem = null;

/* functions called by cropping events */

function showZone() {
	$('#large_scene_image').imgAreaSelect({show:true});
}

function hideAutocompleteBox() {
	$('#ajax_choose_productcustom')
		.fadeOut('fast')
		.find('#custom_autocomplete_input_best').val('');
}

function onSelectEnd(img, selection) {
	selectionCurrent = selection;
	showAutocompleteBox();
}

/*
** Pointer function do handle event by key released
*/
function handlePressedKey(keyNumber, fct)
{
	// KeyDown isn't handled correctly in editing mode
	$(document).keyup(function(event) 
	{	
	  if (event.keyCode == keyNumber)
		 fct();
	});
}

function showAutocompleteBox() {	
	$('#ajax_choose_productcustom:hidden')
	.slideDown('fast');
	$('#custom_autocomplete_input_best').focus();
	handlePressedKey('27', undoEdit);
}

function afterTextInserted (event, data, formatted) {	
	if (data == null)
		return false;
	
	// If the element exist, then the user confirm the editing
	// The variable need to be reinitialized to null for the next
	if (lastEditedItem != null)
		lastEditedItem.remove();
	lastEditedItem = null;
	
	zoneCurrent++;
	var idProduct = data[1];
	var nameProduct = data[0];
	
}

function initAccessoriesAutocompleteCustom() {
	$('#custom_autocomplete_input_best')
		.autocomplete('ajax_products_list.php', {
			minChars: 1,
			autoFill: true,
			max:20,
			matchContains: true,
			mustMatch:true,
			scroll:true,
			cacheLength:0,
			formatItem: function(item) {
				return item[0]+' <img src="../modules/custombestsellers/img/duplicate.gif" class="middle" alt="" />';
			}
		}).result(self.addCustomProduct);

	$('#custom_autocomplete_input_best').setOptions({
		extraParams: {
			excludeIds : self.getAccessoriesIds()
		}
	});
};

function getAccessoriesIds()
{
	if ($('#inputBestProductCustom').val() === undefined)
		return '';
	var ids = $('#inputBestProductCustom').val().replace(/\\-/g,',').replace(/\\,$/,'');
	ids = ids.replace(/\,$/,'');

	return ids;
}

function addCustomProduct(event, data, formatted)
{
	if (data == null)
		return false;
	var productId = data[1];
	var productName = data[0].split('(')[0];

	var input = getE('inputBestProductCustom');
	// Cut hidden fields in array
	var inputCut = input.value.split('-');

	var $slidesCustomContent = $('#slidesCustomContent').find("ul");
	var $inputBestProductCustom = $('#inputBestProductCustom');
	var $inputNameCustom = $('#inputNameCustom');
	if(input.value == ""){
		$('#alert').attr('hidden', 'hidden');
	}
	//validate if exist
	for (i in inputCut){
		if (inputCut[i] == productId){
			var $error =  $slidesCustomContent.find('li[id='+productId+']');
			$error.effect('highlight', {color: '#FF7F84'}, 1000);
			return false;
		}
	}
	var ajax = "";
	$.ajax({
		url: getE('controllerdir').value + "&process=info",
		type: "POST",
		async: false,
		data: {
			'ajax': true,
			'id_product' : productId
		},
		success: function(result)
		{
			if (result != '0'){
				ajax = result;
				
			}
		}
	});
	info = ajax.split('|');
	/* delete product from select + add product line to the div, input_name, input_ids elements */
	$slidesCustomContent.html($slidesCustomContent.html() + '<li style="display: none" id="' + productId + '" stock="' + info[2] + '" available="' + info[3] + '"><img id="img_pro" src="' + info[1] + '" alt="' +productName + '"/><strong> ' + inputCut.length + '</strong> ' +productName.substring(0, 25) + '<p id="actions_img_custom""><span class="delCustomProduct" name="' + productId + '" style="cursor: pointer;"><img src="../modules/custombestsellers/img/delete.gif" class="middle" alt="" /></span></p></li>');
	$slidesCustomContent.find('li[id='+productId+']').show('slow');
	$inputNameCustom.val($inputNameCustom.val() + productName + '¤')
	$inputBestProductCustom.val($inputBestProductCustom.val() + productId + '-');
	$('#custom_autocomplete_input_best').val('');
	$('#custom_autocomplete_input_best').setOptions({
		extraParams: {excludeIds : self.getAccessoriesIds()}
	});
};

function delCustomProduct(id)
{
	var div = $('#slidesCustomContent').find("ul");
	var input = getE('inputBestProductCustom');
	var name = getE('inputNameCustom');
	// Cut hidden fields in array
	var inputCut = input.value.split('-');
	var nameCut = name.value.split('¤');
	var count = 1;
	if (inputCut.length != nameCut.length){
		return jAlert('Bad size');
	}
	input.value = '';
	name.value = '';
	for (i in inputCut){
		// If empty, error, next
		if (!inputCut[i] || !nameCut[i]){
			continue ;
		}
		// Add to hidden fields no selected products OR add to select field selected product
		if (inputCut[i] != id){
			input.value += inputCut[i] + '-';
			name.value += nameCut[i] + '¤';
			div.find('li[id='+inputCut[i]+']').find('strong').html(count);
			count++;
		}else{
			div.find('li[id='+inputCut[i]+']').toggle('slow', function() {
			    $(this).remove();
			});
		}
	}
	if(input.value == ''){
		$('#alert').removeAttr('hidden');
	}
	$('#custom_autocomplete_input_best').setOptions({
		extraParams: {excludeIds : self.getAccessoriesIds()}
	});
};

function updCustomProduct(order)
{
	var div = $('#slidesCustomContent').find("ul");
	var input = getE('inputBestProductCustom');
	var name = getE('inputNameCustom');
	// Cut hidden fields in array
	var inputCut = input.value.split('-');
	var nameCut = name.value.split('¤');
	var count = 1;
	if (inputCut.length != nameCut.length){
		return jAlert('Bad size');
	}
	input.value = '';
	name.value = '';
	var html ="";
	for(i=0;i<order.length;i++)
	{
		var index = inputCut.indexOf(order[i]);
		// If empty, error, next
		if (index >= 0){
		// Add to hidden fields no selected products OR add to select field selected product
		    var index = inputCut.indexOf(order[i]);
			input.value += inputCut[index] + '-';
			name.value += nameCut[index] + '¤';
			var stock = div.find('li[id='+ inputCut[index] +']').attr('stock');
			var available = div.find('li[id='+ inputCut[index] +']').attr('available');
			var img = div.find('li[id='+ inputCut[index] +']').find('img').attr('src');
			html += "<li id=" + inputCut[index] + " stock='" + stock + "' available='" + available + "'><img id='img_pro' src='" + img + "' alt='" +nameCut[i] + "''/><strong> " + count + '</strong> '+ nameCut[index] + '<p id="actions_img_custom"><span class="delCustomProduct" name="' + inputCut[index] + '" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span></p></li>';
			count++;
		}
	}
	div.empty();
	div.append(html);
	$('#custom_autocomplete_input_best').setOptions({
		extraParams: {excludeIds : self.getAccessoriesIds()}
	});
};

function filterCustomReal(stock, available){

		$('#slidesCustomReal li').each(function(){
			if($(this).attr('stock') <= 0 && stock){
				$(this).animate({backgroundColor: '#FF7F84'});
			}else if($(this).attr('available') <= 0 && available){
				$(this).animate({backgroundColor: '#FF7F84'});
			}else{
				$(this).animate({backgroundColor: '#F4E6C9'});
			}
		});

		$('#slidesCustom li').each(function(){
			if($(this).attr('stock') <= 0 && stock){
				$(this).animate({backgroundColor: '#FF7F84'});
			}else if($(this).attr('available') <= 0 && available){
				$(this).animate({backgroundColor: '#FF7F84'});
			}else{
				$(this).animate({backgroundColor: '#F4E6C9'});
			}
		});
}




$(window).load(function () {
	initAccessoriesAutocompleteCustom();
	$('#fill_stock').click(function(){
		filterCustomReal($(this).is(':checked'), $('#fill_available').is(':checked'));
	});
	$('#fill_available').click(function(){
		filterCustomReal($('#fill_stock').is(':checked'), $(this).is(':checked'));
	});
	$('#custom_save').click(function(){
		$('#btnSubmit_list').click();
	});
	$('#custom_save_config').click(function(){
		$('#btnSubmit').click();
	});

	$('#slidesCustomContent').delegate('.delCustomProduct', 'click', function(){
		delCustomProduct($(this).attr('name'));
	});

	$("#false_enter").click(function() {
		e = jQuery.Event("keydown");
		e.which = 13 //enter key
		jQuery('input').trigger(e);
   	});

	$('#slidesCustomReal').delegate('.addCustomProduct', 'click', function(){
		var id = $(this).attr('id');
		var name = $(this).attr('name');
		var data = new Array();
		data[0] = name;
		data[1] = id;
		addCustomProduct(null, data , null);
	});

	prettyPrint($('#custom_layout_left'));
	$(function() {
		var $mySlides = $("#slidesCustom");
		$mySlides.sortable({
			opacity: 0.6,
			cursor: "move",
			update: function() {
				var order = $(this).sortable("toArray");
   				updCustomProduct(order);
				}
			});

		$mySlides.hover(function() {
			$(this).css("cursor","move");
			},
			function() {
			$(this).css("cursor","auto");
		});
	});
	
});