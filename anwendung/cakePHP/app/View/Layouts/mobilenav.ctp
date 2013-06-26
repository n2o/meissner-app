<!--
	Edit here the mobile view for the navigation
 -->
<div data-role="panel" id="nav" data-position="left" data-display="reveal" data-theme="a">
	<h3>Navigation</h3>
	<div data-role="controlgroup">
		<?php echo $this->Html->link('Events', array('controller' => 'events', 'action' => 'index'), array('data-role' => 'button', 'data-theme' => 'a')); ?>
		<?php echo $this->Html->link('Users', array('controller' => 'users', 'action' => 'index'), array('data-role' => 'button', 'data-theme' => 'a')); ?>
	</div>
</div>