<?php
include 'db_connection.php';
?>
<?php

$result = mysql_query("SELECT * FROM locdata") or die(mysql_error()); 


?>

<table border="1" cellpadding="5" cellspacing="5">
<tr> <th>Image</th></tr>

<?php

while($row = mysql_fetch_array($result)) {

$id = $row['loc_id'];

?>
    <tr>

        <td><img src = "<path>/<?php echo $rows['pool.jpg'] ?>" /></td>

   </tr>

<?php   
} 


?>
</table>