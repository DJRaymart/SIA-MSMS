<?php

require_once('fpdf/fpdf.php');
require_once 'db.php';

class PDF extends FPDF {
    
    function Header() {
        
        if (file_exists('export.jpg')) {
            
            list($origWidth, $origHeight) = getimagesize('export.jpg');

            $pageWidth = $this->w; 
            $pageHeight = $this->h; 

            $widthRatio = $pageWidth / $origWidth;
            $heightRatio = $pageHeight / $origHeight;
            $scale = max($widthRatio, $heightRatio); 

            $newWidth = $origWidth * $scale;
            $newHeight = $origHeight * $scale;

            $x = ($this->w - $newWidth) / 2;
            $y = ($this->h - $newHeight) / 2;

            $this->Image('export.jpg', $x, $y, $newWidth, $newHeight);

            $this->SetFillColor(245, 245, 245); 
            $this->Rect(0, 0, $this->w, $this->h, 'F');
        }

        $this->SetFont('Arial', 'B', 15);

        $this->Cell(0, 10, 'LIBRARY MANAGEMENT SYSTEM', 0, 1, 'C');

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 102, 204);
        $this->Cell(0, 10, 'PENALTY RECEIPT', 0, 1, 'C');
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

    function PenaltyReceipt($transaction_id, $user_name, $book_title, $late_penalty, $lost_penalty, $damage_penalty, $total_penalty, $new_balance, $librarian_name) {
        $date = date('F d, Y');
        $time = date('h:i A');

        $book_title = $this->cleanText($book_title);

        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'OFFICIAL PENALTY RECEIPT', 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Receipt Date: ' . $date . ' at ' . $time, 0, 1);
        $this->Cell(0, 10, 'Transaction ID: ' . $transaction_id, 0, 1);
        $this->Cell(0, 10, 'Processed By: ' . $librarian_name, 0, 1);
        $this->Ln(5);

        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'BORROWER INFORMATION', 0, 1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 8, 'Name: ' . $this->cleanText($user_name), 0, 1);
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'BOOK INFORMATION', 0, 1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 8, 'Title: ' . $book_title, 0, 1);
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'PENALTY BREAKDOWN', 0, 1);

        $this->SetFont('Arial', '', 11);

        $this->SetFillColor(220, 220, 220);
        $this->Cell(80, 8, 'Penalty Type', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Amount', 1, 1, 'C', true);

        $this->Cell(80, 8, 'Late Penalty', 1);
        $this->Cell(40, 8, 'PHP +' . number_format($late_penalty, 2), 1, 1, 'R');
        
        $this->Cell(80, 8, 'Lost Book Penalty', 1);
        $this->Cell(40, 8, 'PHP +' . number_format($lost_penalty, 2), 1, 1, 'R');
        
        $this->Cell(80, 8, 'Damage Penalty', 1);
        $this->Cell(40, 8, 'PHP +' . number_format($damage_penalty, 2), 1, 1, 'R');
        
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(180, 180, 180);
        $this->Cell(80, 8, 'TOTAL PENALTY', 1, 0, 'C', true);
        $this->Cell(40, 8, 'PHP +' . number_format($total_penalty, 2), 1, 1, 'R', true);
        
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'ACCOUNT SUMMARY', 0, 1);
        $this->SetFont('Arial', '', 11);
        
        if ($new_balance < 0) {
            
            $this->SetTextColor(255, 0, 0); 
            $this->Cell(0, 8, 'Current Balance Due: PHP ' . number_format($new_balance, 2), 0, 1);
            $this->Cell(0, 8, 'Status: Amount Owed', 0, 1);
        } elseif ($new_balance > 0) {
            
            $this->SetTextColor(0, 128, 0); 
            $this->Cell(0, 8, 'Current Credit Balance: PHP +' . number_format($new_balance, 2), 0, 1);
            $this->Cell(0, 8, 'Status: Credit Available', 0, 1);
        } else {
            
            $this->SetTextColor(0, 0, 255); 
            $this->Cell(0, 8, 'Current Balance: PHP 0.00', 0, 1);
            $this->Cell(0, 8, 'Status: Balance Settled', 0, 1);
        }
        
        $this->SetTextColor(0, 0, 0);

        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, '_________________________', 0, 1, 'C');
        $this->Cell(0, 5, 'Library Staff Signature', 0, 1, 'C');
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

if (isset($_GET['transaction_id'])) {
    $transaction_id = $_GET['transaction_id'];
    
    try {
        
        $sql = "SELECT 
                    t.transaction_id,
                    t.late_penalty,
                    t.lost_penalty,
                    t.damage_penalty,
                    t.total_penalty,
                    t.penalty_updated_at,
                    b.title as book_title,
                    u.full_name as user_name,
                    u.balance as new_balance,
                    l.full_name as librarian_name
                FROM borrowing_transaction t
                JOIN book b ON t.book_id = b.book_id
                JOIN tbl_users u ON t.user_id = u.user_id
                LEFT JOIN librarian l ON t.librarian_id = l.librarian_id
                WHERE t.transaction_id = :transaction_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':transaction_id' => $transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            
            $pdf = new PDF();
            $pdf->AliasNbPages();
            $pdf->AddPage();

            $pdf->PenaltyReceipt(
                $transaction['transaction_id'],
                $transaction['user_name'],
                $transaction['book_title'],
                $transaction['late_penalty'],
                $transaction['lost_penalty'],
                $transaction['damage_penalty'],
                $transaction['total_penalty'],
                $transaction['new_balance'],
                $transaction['librarian_name'] ?? 'Library System'
            );

            $pdf->Output('D', 'Penalty_Receipt_' . $transaction_id . '_' . date('Y-m-d') . '.pdf');
        } else {
            die('Transaction not found!');
        }
        
    } catch (PDOException $e) {
        die('Database error: ' . $e->getMessage());
    }
} else {
    die('No transaction ID provided!');
}
?>