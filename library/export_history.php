<?php

require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT full_name, institutional_id FROM tbl_users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $conn->prepare("
    SELECT bt.*, b.title, b.author, b.category, b.isbn
    FROM borrowing_transaction bt
    JOIN book b ON bt.book_id = b.book_id
    WHERE bt.user_id = ? 
    ORDER BY bt.borrow_date DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

$format = $_POST['format'] ?? 'csv';
$format = in_array($format, ['csv', 'pdf']) ? $format : 'csv';

if ($format === 'csv') {
    exportToCSV($history, $user);
} else {
    exportToPDF($history, $user);
}

function exportToCSV($history, $user) {
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="library_history_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, ['Library Borrowing History Report']);
    fputcsv($output, ['']);
    fputcsv($output, ['User:', $user['full_name']]);
    fputcsv($output, ['Student ID:', $user['institutional_id']]);
    fputcsv($output, ['Generated:', date('F j, Y H:i:s')]);
    fputcsv($output, ['']);

    $headers = [
        'Transaction ID',
        'Book Title',
        'Author',
        'Category',
        'ISBN',
        'Borrow Date',
        'Due Date',
        'Return Date',
        'Status',
        'Penalty ($)',
        'Days Borrowed',
        'Return Status'
    ];
    fputcsv($output, $headers);

    foreach ($history as $record) {
        $days_borrowed = 'N/A';
        $return_status = 'N/A';
        
        if ($record['borrow_date'] && $record['return_date']) {
            $borrow = new DateTime($record['borrow_date']);
            $return = new DateTime($record['return_date']);
            $days_borrowed = $borrow->diff($return)->days;
            $return_status = $record['return_date'] <= $record['due_date'] ? 'On Time' : 'Late';
        }
        
        $row = [
            $record['transaction_id'],
            htmlspecialchars_decode($record['title']),
            htmlspecialchars_decode($record['author']),
            $record['category'] ?? 'N/A',
            $record['isbn'] ?? 'N/A',
            $record['borrow_date'],
            $record['due_date'],
            $record['return_date'] ?? 'Not Returned',
            $record['status'],
            number_format($record['total_penalty'], 2),
            $days_borrowed,
            $return_status
        ];
        
        fputcsv($output, $row);
    }

    fputcsv($output, ['']);
    fputcsv($output, ['Summary Statistics']);
    fputcsv($output, ['Total Books:', count($history)]);
    
    $returned = array_filter($history, fn($r) => $record['status'] === 'Returned');
    fputcsv($output, ['Returned:', count($returned)]);
    
    $overdue = array_filter($history, fn($r) => $record['status'] === 'Overdue');
    fputcsv($output, ['Overdue:', count($overdue)]);
    
    $total_penalty = array_sum(array_column($history, 'total_penalty'));
    fputcsv($output, ['Total Penalties:', '$' . number_format($total_penalty, 2)]);
    
    fclose($output);
    exit;
}

function exportToPDF($history, $user) {

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="library_history_' . date('Y-m-d') . '.pdf"');

    echo "<html>
    <head>
        <title>Library Borrowing History</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            h1 { color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #f2f2f2; padding: 10px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .header { margin-bottom: 30px; }
            .summary { margin-top: 30px; padding: 20px; background-color: #f9f9f9; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Library Borrowing History Report</h1>
            <p><strong>User:</strong> " . htmlspecialchars($user['full_name']) . "</p>
            <p><strong>Student ID:</strong> " . htmlspecialchars($user['institutional_id']) . "</p>
            <p><strong>Generated:</strong> " . date('F j, Y H:i:s') . "</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Penalty</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($history as $record) {
        echo "<tr>
                <td>#" . $record['transaction_id'] . "</td>
                <td>" . htmlspecialchars($record['title']) . "</td>
                <td>" . htmlspecialchars($record['author']) . "</td>
                <td>" . $record['borrow_date'] . "</td>
                <td>" . $record['due_date'] . "</td>
                <td>" . ($record['return_date'] ?: 'Not Returned') . "</td>
                <td>" . $record['status'] . "</td>
                <td>$" . number_format($record['total_penalty'], 2) . "</td>
              </tr>";
    }
    
    echo "</tbody>
        </table>
        
        <div class='summary'>
            <h3>Summary</h3>
            <p>Total Books: " . count($history) . "</p>
            <p>Total Penalties: $" . number_format(array_sum(array_column($history, 'total_penalty')), 2) . "</p>
        </div>
        
        <script>
            // Auto-print and close for PDF simulation
            window.onload = function() {
                window.print();
                setTimeout(function() {
                    window.close();
                }, 1000);
            }
        </script>
    </body>
    </html>";
    
    exit;
}