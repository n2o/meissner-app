<article>
	<h1>Edit Post</h1>
	<p>
		<?php 
			echo $this->Form->create('Post');
			echo $this->Form->input('title');
			echo $this->Form->input('body', array('rows' => '3'));
			echo $this->Form->input('id', array('type' => 'hidden'));
			echo $this->Form->end('Save Post');
		?>
	</p>
</article>