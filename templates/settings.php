<div class="wrap">
	<h2>Add Words to Filter</h2>
	
	<?php 
		global $wpdb;

		//ADD WORD FILTER TO DB _POST
		if(isset($_POST['submitted']) == 1) {
			// $original 		= isset($_POST['original']) 	? $_POST['original'] : Array();
			// $replacement 	= isset($_POST['replacement']) 	? $_POST['replacement'] : Array();
			$original = $_POST['original'];
			$replacement = $_POST['replacement'];


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
		}//END ADD WORD FILTER TO DP _POST

		//DELETE BUTTON _POST
		if(isset($_POST['deleted'])){
			if($_POST['deleted'] >= 1){
				$wpdb->query($wpdb->prepare("
					DELETE FROM ".$wpdb->prefix."comment_filter"." 
					WHERE id=".$_POST['deleted']
				));
				echo "Word Filter deleted.";
			}
		} //END of DELETE BUTTON _POST

	 ?>
	<?php $action_url = admin_url('options-general.php?page=commentfilter'); ?>
	<!--Comment WordSwap Add Filter Form -->
	<form action="<?php echo $action_url;?>" method="post" role="form">

		<div class="form-group">

			<label for="original">Original:</label>
			<input class="form-control" type="text" name="original" id="original" placeholder="Original">

		</div>

		<div class="form-group">

			<label for="replacement">Filter:</label>
			<input class="form-control" type="text" name="replacement" id="replacement" placeholder="Replacement">

		</div>
		
		<button type="submit" class="btn btn-default">Add Word Filter To Database</button>
		<input type="hidden" name="submitted" value="1">

	</form><!--END of CommentSwap Add Filter Form-->

	<!--Comment WordSwap Tables: Show Words that are in DB that are being filtered/swapped.-->
	<table class="widefat fixed" width="650" align="center" width="100%" id="word-replacer-list">
		<thead>
			<tr>
				<th>Original</th>
				<th>Filter</th>
				<th>Delete</th>
			</tr>
		</thead>
		<?php 
			global $wpdb;
			$comment_filter_db = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."comment_filter"." ORDER BY id", ARRAY_A);

			foreach ($comment_filter_db as $cfdb) { ?>
				<tr>
					<td><?php echo $cfdb['original'] ?></td>
					<td><?php echo $cfdb['replacement'] ?></td>
					<td>
						<form action="<?php echo $action_url;?>" method="post" role="form">
							<button type="submit" class="btn btn-default">Delete</button>
							<input type="hidden" name="deleted" value="<?php echo $cfdb['id']; ?>">
						</form>
					</td>
				</tr>
			<?php }	?>
	</table><!--END of CommentSwap Tables-->

</div>