
function pkgTypesAssign() {
   $(".pkg_type_name").find("input").autocomplete({source: []});
   $(".pkg_type_hs_code").find("input").autocomplete({source: []});

   $(".pkg_type_name").find("input").autocomplete({
     source: function( request, response ) {
         $.ajax({
                  url:"/w-packages/typenames",
                  type: 'post',
                  dataType: "json",
                  data: {
                           search: request.term,
                           by: "name",
			   id: $(this.element).parent().prop("id")
                  },
                  success: function( data ) {
                            response( data );
              }
         });
     },
    select: function (event, ui) {
	let arr=ui.item.id.split('-');
	let num=arr[1];
        $('#pkg_type_name-'+num).find("input").val(ui.item.name);
        $('#pkg_type_hs_code-'+num).find("input").val(ui.item.hs_code);
        $('#pkg_type_id-'+num).val(ui.item.value);
        return false;
     }
  });

   $(".pkg_type_hs_code").find("input").autocomplete({
     source: function( request, response ) {
         $.ajax({
                  url:"/w-packages/typenames",
                  type: 'post',
                  dataType: "json",
                  data: {
                           search: request.term,
                           by: "hs_code",
			   id: $(this.element).parent().prop("id")
                  },
                  success: function( data ) {
                            response( data );
              }
         });
     },
    select: function (event, ui) {
	let arr=ui.item.id.split('-');
	let num=arr[1];
        $('#pkg_type_name-'+num).find("input").val(ui.item.name);
        $('#pkg_type_hs_code-'+num).find("input").val(ui.item.hs_code);
        $('#pkg_type_id-'+num).val(ui.item.value);
        return false;
     }
  });
}


function firstSelectChange(firstSelect,nid=0,secondSelect=null) {
    let id=nid;
    if(id==0)
       id = firstSelect.val();
    let children = $.grep(categories, function(e) {return e.parent_id == id;});
    let html = '<option value="0">-</option>';
    let nameAttr ='name_en';
    if(secondSelect==null)
        nameAttr=firstSelect.parent().find('.secondselect').attr('data-attributes');
    else
        nameAttr=secondSelect.attr('data-attributes');
    $.each(children, function(id, values) {
      html = html + '<option value="' + values['id'] + '">' + values[nameAttr] + '</option>';
    });
    if(secondSelect==null)
        firstSelect.parent().find('.secondselect').html(html);
    else
        secondSelect.html(html);
}

$(document).on('change','.firstselect',function() {
    firstSelectChange($(this));
});


$(document).ready(function () {
    if (typeof keys !== 'undefined' && keys.length > 0) {
            let key=keys[0];
	    $("#main_key_item").find('input[name=keys\\[\\]]').val(key[0]);
	    $("#main_key_item").find('select[name=types\\[\\]]').val(key[1]);
            for (let ki = 1; ki < keys.length; ki++) {
                key=keys[ki];
                let clone = $("#main_key_item").clone();
	        clone.find('input[name=keys\\[\\]]').val(key[0]);
	        clone.find('select[name=types\\[\\]]').val(key[1]);
                clone.removeAttr("id");
                clone.addClass("extra_type");
                $("#key_section").append(clone);
            }
    }
    if (typeof goods !== 'undefined' && goods.length > 0) {
            let good=goods[0];
	    if(w_id==12) {
	       $("#main_type_item").find('select[name=ru_types\\[\\]]').val(good[0]).select2();
               $("#main_type_item").find('input[name=ru_types\\[\\]]').val(good[0]);
               $("#main_type_item").find('input[name=ru_types\\[\\]]').attr("id","pkg_type_id-"+pkgTypeNum);
               $("#main_type_item").find('input[name=ru_shipping_amounts\\[\\]]').val(good[1]);
               $("#main_type_item").find('input[name=ru_weights\\[\\]]').val(good[2]);
               $("#main_type_item").find('input[name=ru_items\\[\\]]').val(good[3]);
               $("#main_type_item").find('input[name=ru_hscodes\\[\\]]').val(good[4]);
               $("#main_type_item").find('input[name=ru_names\\[\\]]').val(good[5]);
	    }
	    else {
	       if(good[0]==1) {
	           $("#main_type_item").find('select[name=customs_type_parents\\[\\]]').val(good[1]).select2();
                   $("#main_type_item").find('input[name=customs_type_parents\\[\\]]').val(good[1]);
                   $("#main_type_item").find('input[name=customs_type_parents\\[\\]]').attr("id","pkg_type_id-"+pkgTypeNum);
                   firstSelectChange($("#main_type_item").find('input[name=customs_type_parents\\[\\]]'),good[1],$("#main_type_item").find('select[name=customs_types\\[\\]]'));
	           $("#main_type_item").find('select[name=customs_types\\[\\]]').val(good[2]).select2();
                   $("#main_type_item").find('input[name=customs_types\\[\\]]').val(good[2]);
                   $("#main_type_item").find('input[name=customs_types\\[\\]]').attr("id","pkg_type_id-"+pkgTypeNum);
            	   $("#main_type_item").find('input[name=ru_shipping_amounts\\[\\]]').val(good[3]);
            	   $("#main_type_item").find('input[name=ru_weights\\[\\]]').val(good[4]);
            	   $("#main_type_item").find('input[name=ru_items\\[\\]]').val(good[5]);
	       }
	    }
	    pkgTypeNum=1;
            for (let gi = 1; gi < goods.length; gi++) {
                good=goods[gi];
                let clone = $("#main_type_item").clone();
                clone.removeAttr("id");
                clone.addClass("extra_type");
                clone.find(".select2-container").remove();
	        if(w_id==12) {
		    clone.find('select[name=ru_types\\[\\]]').val(good[0]).select2();
                    clone.find('input[name=ru_types\\[\\]]').val(good[0]);
                    clone.find('input[name=ru_types\\[\\]]').attr("id","pkg_type_id-"+pkgTypeNum);
                    clone.find('input[name=ru_shipping_amounts\\[\\]]').val(good[1]);
                    clone.find('input[name=ru_weights\\[\\]]').val(good[2]);
                    clone.find('input[name=ru_items\\[\\]]').val(good[3]);
                    clone.find('input[name=ru_hscodes\\[\\]]').val(good[4]);
                    clone.find('input[name=ru_names\\[\\]]').val(good[5]);
                    clone.find(".pkg_type_name").attr('id','pkg_type_name-'+pkgTypeNum);
                    clone.find(".pkg_type_hs_code").attr('id','pkg_type_hs_code-'+pkgTypeNum);
		} else {
	            if(good[0]==1) {
		        clone.find('select[name=customs_type_parents\\[\\]]').val(good[1]).select2();
                        clone.find('input[name=customs_type_parents\\[\\]]').val(good[1]);
                        clone.find('input[name=customs_type_parents\\[\\]]').attr("id","pkg_type_id-"+pkgTypeNum);
                   	firstSelectChange(clone.find('input[name=customs_type_parents\\[\\]]'),good[1],clone.find('select[name=customs_types\\[\\]]'));
		        clone.find('select[name=customs_types\\[\\]]').val(good[2]).select2();
                        clone.find('input[name=customs_types\\[\\]]').val(good[2]);
                        clone.find('input[name=customs_types\\[\\]]').attr("id","pkg_type_id-"+pkgTypeNum);
                    	clone.find('input[name=ru_shipping_amounts\\[\\]]').val(good[3]);
                    	clone.find('input[name=ru_weights\\[\\]]').val(good[4]);
                    	clone.find('input[name=ru_items\\[\\]]').val(good[5]);
		    }
		}
	        pkgTypeNum++;
                clone.find(".pkg_type_id").attr('id','pkg_type_id-'+pkgTypeNum);
                $("#type_section").append(clone);
            }
    }

    pkgTypesAssign();

});
