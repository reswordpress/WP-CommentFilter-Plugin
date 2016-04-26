<div class="wrap">
	<h2>Add Words to Filter</h2>
	
	<?php 
		//SUBMIT WORD FILTER TO DB
		if(isset($_POST['submitted']) == 1) {
			// $original 		= isset($_POST['original']) 	? $_POST['original'] : Array();
			// $replacement 	= isset($_POST['replacement']) 	? $_POST['replacement'] : Array();
			$original = $_POST['original'];
			$replacement = $_POST['replacement'];

		global $wpdb;

			if(!empty($original)){
				$wpdb->query($wpdb->prepare("
					INSERT INTO ".$wpdb->prefix."comment_filter"." 
					(original, replacement)
					VALUES (%s, %s)",
					array(
					//esc_sql(base64_encode(trim($original[$i]))),
					esc_sql(trim($original)),
					esc_sql(trim($replacement)),
					))); 

				$message = '<div id="message" class="updated fade"><p><strong>Word Filter Added to Database.</strong></p></div>';
				echo $message;
			}else{ 
				echo "Word Filter not Added. Original field was left empty.";
			}
		}

	 ?>
	<?php $action_url = admin_url('options-general.php?page=commentfilter'); ?>
	<!--CommentSwap Word Replacement Form -->
	<form action="<?php echo $action_url;?>" method="post" role="form">

		<div class="form-group">

			<label for="original">Original:</label>
			<input class="form-control" type="text" name="original" id="original" placeholder="Original">

		</div>

		<div class="form-group">

			<label for="replacement">Replacement:</label>
			<input class="form-control" type="text" name="replacement" id="replacement" placeholder="Replacement">

		</div>
		
		<button type="submit" class="btn btn-default">Add Word Filter To Database</button>
		<input type="hidden" name="submitted" value="1">

	</form>


</div>