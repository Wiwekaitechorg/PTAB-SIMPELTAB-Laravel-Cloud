<?php

function rupiah($harga)
{
    $RP = 'Rp. ' . number_format($harga, 0, ',', '.');
    return $RP;
}
