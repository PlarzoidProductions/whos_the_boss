<table width="80%" class="basic_table" align="center">
	<tr><td colspan=3><h1>SURPRISES</h1></td></tr>
	<tr><td colspan=3><hr/></td></tr>
	<?php foreach($surprises as $s) { ?>
		<tr>
			<td><?php echo $page->displayVar("checkbox".$s[id]) ?></td>
			<td><?php echo $s[name] ?></td>
			<td><?php echo $surprise_rule[$s[system]] ?></td>
		</tr>
	<?php } ?>
</table>
