<?php
class StockStatus {
    public static function getStatus($stock, $min_stock, $max_stock) {
        $low = $min_stock * 0.25;
        $low_lev = $min_stock - $low;
        $high = $max_stock * 0.25;
        $high_lev = $max_stock - $high;

        if ($stock <= $low_lev) {
            $status = 'Very Low';
            $backgroundColor = '#cc0000';
            $textColor = 'text-white';
        } else if ($stock >= $low_lev && $stock <= $min_stock) {
            $status = 'Low';
            $backgroundColor = '#ff4500';
            $textColor = 'text-white';
        } else if ($stock >= $min_stock && $stock <= $high_lev) {
            $status = 'Medium';
            $backgroundColor = '#ffff00';
            $textColor = 'text-black'; // Warna teks menjadi hitam untuk status Medium.
        } else if ($stock >= $high_lev && $stock <= $max_stock) {
            $status = 'High';
            $backgroundColor = '#469536';
            $textColor = 'text-white';
        } else {
            $status = 'Very High';
            $backgroundColor = '#006600';
            $textColor = 'text-white';
        }

        $formattedStock = number_format($stock, 0, '.', '.');

        return [
            'status' => $status,
            'formattedStock' => $formattedStock,
            'backgroundColor' => $backgroundColor,
            'textColor' => $textColor // Menambahkan warna teks ke hasil yang dikembalikan.
        ];
    }
}

?>