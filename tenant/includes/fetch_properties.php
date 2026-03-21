<?php

require_once "../../config/db.php";

/*
Fetch properties visible to tenants
*/

$query = "
SELECT 
    p.property_id,
    p.title,
    p.rent_amount,
    p.property_type,
    p.town,
    p.has_wifi,
    p.has_parking,
    p.is_featured
FROM properties p

JOIN users u 
ON p.landlord_id = u.user_id

WHERE 
    p.status='available'
AND p.moderation_status='approved'
AND u.is_verified=1

ORDER BY p.is_featured DESC, p.created_at DESC
";

$result = $conn->query($query);

$properties = [];

if($result && $result->num_rows > 0){

    while($row = $result->fetch_assoc()){
        $properties[] = $row;
    }

}

?>