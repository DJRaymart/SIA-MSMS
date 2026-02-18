<?php

require_once('fpdf/fpdf.php');
require_once 'db.php';

class BalancePDF extends FPDF {
    
    function Header() {
        
        $this->SetFont('Arial', 'B', 15);

        $this->Cell(0, 10, 'LIBRARY MANAGEMENT SYSTEM', 0, 1, 'C');

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 102, 204);
        $this->Cell(0, 10, 'BALANCE ADJUSTMENT RECEIPT', 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);

        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer() {
        
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function BalanceAdjustmentReceipt($receipt_id, $transaction_date, $user_name, $user_id, $institutional_id, 
                                      $adjustment_type, $amount, $previous_balance, $new_balance, 
                                      $librarian_name, $reason, $notes = '') {
        $date = date('F d, Y');
        $time = date('h:i A');

        $user_name = $this->cleanText($user_name);
        $reason = $this->cleanText($reason);
        $librarian_name = $this->cleanText($librarian_name);

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Transaction Date: ' . $transaction_date, 0, 1);
        $this->Cell(0, 10, 'Processed By: ' . $librarian_name, 0, 1);
        $this->Ln(10);

        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'USER INFORMATION', 0, 1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 8, 'Name: ' . $user_name, 0, 1);
        $this->Cell(0, 8, 'User ID: ' . $user_id, 0, 1);
        $this->Cell(0, 8, 'Student / Employee ID: ' . $institutional_id, 0, 1);
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'ADJUSTMENT DETAILS', 0, 1);

        $this->SetFont('Arial', '', 11);

        $this->SetFillColor(220, 220, 220);
        $this->Cell(80, 8, 'Description', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Amount', 1, 1, 'C', true);

        $adjustment_label = ($adjustment_type == 'add_debt') ? 'Debt Added' : 'Credit Added';
        $this->Cell(80, 8, $adjustment_label, 1);
        $this->Cell(40, 8, ($adjustment_type == 'add_debt' ? 'PHP +' : 'PHP +') . number_format($amount, 2), 1, 1, 'R');
        
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(180, 180, 180);
        $this->Cell(80, 8, 'ADJUSTMENT AMOUNT', 1, 0, 'C', true);
        $this->Cell(40, 8, ($adjustment_type == 'add_debt' ? 'PHP +' : 'PHP +') . number_format($amount, 2), 1, 1, 'R', true);
        
        $this->Ln(10);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'BALANCE SUMMARY', 0, 1);
        
        $this->SetFont('Arial', '', 11);

        $this->Cell(100, 8, 'Previous Balance:', 0);
        if ($previous_balance > 0) {
            $this->SetTextColor(0, 128, 0);
            $this->Cell(0, 8, 'PHP +' . number_format($previous_balance, 2), 0, 1, 'R');
        } elseif ($previous_balance < 0) {
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0, 8, 'PHP ' . number_format($previous_balance, 2), 0, 1, 'R');
        } else {
            $this->SetTextColor(0, 0, 255);
            $this->Cell(0, 8, 'PHP ' . number_format($previous_balance, 2), 0, 1, 'R');
        }

        $this->SetTextColor(0, 0, 0);
        $adjustment_text = ($adjustment_type == 'add_debt') ? 'Debt Added:' : 'Credit Added:';
        $this->Cell(100, 8, $adjustment_text, 0);
        if ($adjustment_type == 'add_debt') {
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0, 8, 'PHP +' . number_format($amount, 2), 0, 1, 'R');
        } else {
            $this->SetTextColor(0, 128, 0);
            $this->Cell(0, 8, 'PHP +' . number_format($amount, 2), 0, 1, 'R');
        }

        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(100, 10, 'NEW BALANCE:', 0);
        
        if ($new_balance > 0) {
            $this->SetTextColor(0, 128, 0);
            $this->Cell(0, 10, 'PHP +' . number_format($new_balance, 2), 0, 1, 'R');
        } elseif ($new_balance < 0) {
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0, 10, 'PHP ' . number_format($new_balance, 2), 0, 1, 'R');
        } else {
            $this->SetTextColor(0, 0, 255);
            $this->Cell(0, 10, 'PHP ' . number_format($new_balance, 2), 0, 1, 'R');
        }
        
        $this->SetTextColor(0, 0, 0);

        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 8, 'Status:', 0);
        $this->SetFont('Arial', '', 11);
        
        if ($new_balance > 0) {
            $this->SetTextColor(0, 128, 0);
            $this->Cell(0, 8, 'Credit Available', 0, 1, 'R');
        } elseif ($new_balance < 0) {
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0, 8, 'Amount Owed', 0, 1, 'R');
        } else {
            $this->SetTextColor(0, 0, 255);
            $this->Cell(0, 8, 'Balance Settled', 0, 1, 'R');
        }
        
        $this->SetTextColor(0, 0, 0);
        $this->Ln(10);

        if (!empty($reason)) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'REASON FOR ADJUSTMENT', 0, 1);
            $this->SetFont('Arial', '', 11);
            $this->MultiCell(0, 8, $reason, 0, 'L');
            $this->Ln(5);
        }

        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 5, '_________________________', 0, 0, 'C');
        $this->Cell(95, 5, '_________________________', 0, 1, 'C');
        $this->Cell(95, 5, 'User Acknowledgement', 0, 0, 'C');
        $this->Cell(95, 5, 'Library Staff Signature', 0, 1, 'C');
    }

    function cleanText($text) {
        
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $replacements = array(
            '&rsquo;' => "'",
            '&€™' => "'",
            '&rsquo;' => "'",
            '&#39;' => "'",
            '&apos;' => "'",
            '&quot;' => '"',
            '&amp;' => '&',
            '&lt;' => '<',
            '&gt;' => '>',
            '&nbsp;' => ' ',
            '€' => 'EUR',
            '£' => 'GBP',
            '¥' => 'JPY',
            '¢' => 'c',
        );
        
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);

        $text = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $text);
        
        return $text;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_balance_receipt'])) {
    
    $receipt_id = $_POST['receipt_id'] ?? 'BA' . date('YmdHis');
    $transaction_date = $_POST['transaction_date'] ?? date('F d, Y h:i A');
    $user_name = $_POST['user_name'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $institutional_id = $_POST['institutional_id'] ?? '';
    $adjustment_type = $_POST['adjustment_type'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $previous_balance = floatval($_POST['previous_balance'] ?? 0);
    $new_balance = floatval($_POST['new_balance'] ?? 0);
    $librarian_name = $_POST['librarian_name'] ?? 'Library Staff';
    $reason = $_POST['reason'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (empty($user_name) || empty($user_id) || empty($adjustment_type) || $amount <= 0) {
        die('Missing required receipt data!');
    }

    $pdf = new BalancePDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->BalanceAdjustmentReceipt(
        $receipt_id,
        $transaction_date,
        $user_name,
        $user_id,
        $institutional_id,
        $adjustment_type,
        $amount,
        $previous_balance,
        $new_balance,
        $librarian_name,
        $reason,
        $notes
    );

    $filename = 'Balance_Adjustment_' . $user_name . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output('D', $filename);
    
} else {
    die('No receipt data provided!');
}