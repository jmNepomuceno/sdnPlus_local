<?php

function fetch_municipality()
{
    $sql_muncity = "SELECT municipality_code, city.province_code, CONCAT(municipality_description,' (', province_description, ')') AS municipality FROM dbo.city INNER JOIN dbo.province ON city.province_code = province.province_code WHERE municipality_description <> '(UNKNOWN)'";
    $result = array();

    $sql_muncity = $sql_muncity." ORDER BY municipality_description";

    $stmt = execute_query($sql_muncity, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        array_push($result, array("code" => $row["municipality_code"], "name" => $row["municipality"]));
    }

    $stmt = null;
    unset($stmt);

    return $result;
}

function generate_set_field(string $field, mixed $value, bool $hasQuote)
{
    return array("field" => $field, "value" => $value, "hasQuote" => $hasQuote);
}

function generate_insert_fields(array $fields) : string
{
    $result = "";

    $fieldArr = array();
    $valueArr = array();
    
    foreach ($fields as $field)
    {
        array_push($fieldArr, $field["field"]);

        if ($field["hasQuote"] == true)
        {
            array_push($valueArr, "'".$field["value"]."'");
        }
        else
        {
            array_push($valueArr, $field["value"]);
        }
    }

    $result = "(".implode(", ", $fieldArr).") VALUES (".implode(", ", $valueArr).")";

    return $result;
}

function generate_update_fields(array $fields) : string
{
    $result = "";
    $tempArr = array();

    foreach ($fields as $field)
    {
        $set_field = $field["field"]." = ";

        if ($field["hasQuote"] == true)
        {
            $set_field = $set_field."'".$field["value"]."'";
        }
        else
        {
            $set_field = $set_field.$field["value"];
        }

        array_push($tempArr, $set_field);
    }

    $result = implode(", ", $tempArr);

    return $result;
}

?>