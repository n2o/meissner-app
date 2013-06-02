<article>
	<h1>Blog posts</h1>
	<?php 
		echo $this->Html->link('Add Post', array('controller' => 'posts', 'action' => 'add'));
	 ?>
	<table>
	    <tr>
	        <th>Id</th>
	        <th>Title</th>
	        <th>Do some magic</th>
	        <th>Created</th>
	    </tr>
	    <!-- Here is where we loop through our $posts array, printing out post info -->
		<?php foreach ($posts as $post): ?> 
		<tr>
			<td>
				<?php echo $post['Post']['id']; ?>
			</td> 
			<td>
				<?php echo $this->Html->link($post['Post']['title'], array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?>
			</td>
			<td>
				<?php echo $this->Html->link('Edit', array('action' => 'edit', $post['Post']['id']));?>
				<?php echo $this->Form->postLink(	# postLink uses javascript to do a post request
					'Delete',
					array('action' => 'delete', $post['Post']['id']),
					array('confirm' => 'Are you sure?')); 
				?>
			</td>
			<td>
				<?php echo $post['Post']['created']; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	<?php unset($post); ?> </table>
</article>