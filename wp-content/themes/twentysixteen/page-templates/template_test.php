<?php 
/** Template Name: Test
**/
acf_form_head();

get_header();

?>
<div id="content">
<?php 

$myarr = array();

  /* $arrayOfObj = array(
[0] => stdClass Object
        (
            [prod_id] => 157
            [quantity] => 1
            [wishlist_id] => 
            [dateadded] => 2018-09-26 11:15:40
        ),

    [1] => stdClass Object
        (
            [prod_id] => 151
            [quantity] => 1
            [wishlist_id] => 
            [dateadded] => 2018-09-26 11:15:44
        )

); */

$arr = array(
 "prod_id" => 157,
 "quantity" => 1,
 "wishlist_id" => "",
 "dateadded" => "2018-09-26 11:15:40",
);

$myarr[] = (object)$arr;

//echo "<pre>"; print_r($myarr);
$arrjson = json_encode($myarr);

echo $arrjson;

//echo "<pre>";print_r($arrayOfObj);

?>
	
	<?php
	
	/* acf_form(array(
		'post_id'		=> 'new_post',
		'post_title'	=> true,
		'post_content'	=> true,
		'new_post'		=> array(
			'post_type'		=> 'post',
			'post_status'	=> 'publish'
		),
		'updated_message' => 'Post updated.',
		'submit_value' => 'Submit',
	)); */
	
	?>
	
</div>

<?php get_footer(); ?>