<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//Get all users
$users = $Account->get_users(array());

?>
<h1 class="title">Users</h1>

<!-- Scripts for table sorting -->
<script type="text/javascript" src="../assets/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../assets/js/DT_bootstrap.js"></script>
<script type="text/javascript">
  /* Table initialisation */
  $(function() {
		$('#users').dataTable( {
			"sDom": "<'row-fluid fix-dt'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
			"sPaginationType": "bootstrap",
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"aoColumns": [
			    {
			        "bSearchable": false,
			        "bSortable": true
			    },
			    null,
			    null,
			    null,
			    null,
			    {
			        "bSearchable": false ,
			        "bSortable": false
			    },
			    null,
			    {
			        "bSearchable": false ,
			        "bSortable": false
			    },
			]
		});
	});
</script>
<style type="text/css"> span .icon-trash, span .icon-edit, span .icon-envelope{ cursor: pointer; }</style>
<!-- Display the table -->
<table class="table table-hover table-striped table-bordered" id="users">
	<thead>
    	<tr>
	    	<th>#</th>
			<th>Username</th>
			<th>Email</th>
			<th>Role</th>
			<th>Status</th>
			<th>Connected</th>
			<th>Joined</th>
			<th>Actions</th>
    	</tr>
  	</thead>
  	<tbody>
  	<?php
  		//If the $user array is not empty
  		if ($users and !empty($users)) {
  			//Loop the users 
  			foreach ($users as $key => $user) {

  				//Get some meta options
  				$role = $Account->get_meta($user['id'], 'role');
  				$fb = $Account->get_meta($user['id'], 'facebook');
				  $tw = $Account->get_meta($user['id'], 'twitter');
				  $go = $Account->get_meta($user['id'], 'google');

				//Display a row with user data
  				echo '<tr>
  					<td data-id="'.$user['id'].'">'. $user['id'] .'</td>
  					<td><a href="../?page=profile&id='.$user['id'].'" target="_blank">'. $user['username'] .'</a></td>
  					<td><a href="mailto:'. $user['email'] .'" target="_blank">'. $user['email'] .'</td>
  					<td>'; 
  					switch ($role) {
  						case 'user': echo '<span class="label">user</span>'; break;
  						case 'admin': echo '<span class="label label-info">admin</span>'; break;
  						default: echo '<span class="label">'.$role.'</span>'; break;
  					}
  					echo'</td>
  					<td>';
  					switch ($user['status']) {
  						case 1: echo '<span class="label label-success">activated</span>'; break;
  						case 2: echo '<span class="label label-important">banned</span>'; break;
  						default: echo '<span class="label label-warning">unactivated</span>'; break;
  					}
  					echo '</td>
  					<td>';
  					if (!empty($fb))
  						echo '<a href="http://www.facebook.com/profile.php?id='.$fb.'" title="Account connected to Facebook"><i class="icon-fb"></i></a>';
  					
  					if (!empty($tw))
  						echo ' <a href="https://twitter.com/account/redirect_by_id?id='.$tw.'"  title="Account connected to Twitter"><i class="icon-tw"></i></a>';
  					
  					if (!empty($go))
  						echo ' <a href="https://profiles.google.com/'.$go.'"  title="Account connected to Google+"><i class="icon-go"></i></a>';
  					
  					echo'</td>
  					<td>'. date('M j, Y', strtotime($user['registered'])) .'</td>
  					<td>
  						<a href="?page=edit_user&id='.$user['id'].'"> <i class="icon-edit"></i></a>
              <span title="Send Email" onclick="Admin.compose(\''.$user['email'].'\');" ><i class="icon-envelope"></i></span>
  						<span onclick="Admin.confirm_delete_user(\''.$user['id'].'\', \''.$user['username'].'\');" title="Delete user"><i class="icon-trash"></i></span>
  					</td>
  				</tr>';
  			}
  		}
  	?>
  	</tbody>
</table>
 
<!-- Modal for delete confirmation -->
<div id="deleteUserModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="delete" aria-hidden="true">
  <input type="hidden" id="user_id" value="">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3>Confirm action</h3>
  </div>
  <div class="modal-body">
    <p>Are you sure you want to delete user <strong id="username"></strong> ?</p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
    <button class="btn btn-primary" onclick="Admin.delete_user( document.getElementById('user_id').value );">Yes</button>
  </div>
</div>