
        function showPrintPreview() {

            const printTemplate = document.querySelector('.print-template');

            const previewContent = printTemplate.cloneNode(true);
            previewContent.classList.add('preview-print-template');
            previewContent.style.display = 'block';
            previewContent.style.position = 'relative';
            previewContent.style.margin = '20px auto';
            previewContent.style.padding = '20mm';
            previewContent.style.background = 'white';
            previewContent.style.fontFamily = "'Times New Roman', serif";
            previewContent.style.fontSize = '12pt';
            previewContent.style.lineHeight = '1.5';
            previewContent.style.boxShadow = '0 0 20px rgba(0,0,0,0.1)';

            const watermark = previewContent.querySelector('.watermark-container');
            if (watermark) {
                watermark.style.position = 'absolute';
                watermark.style.opacity = '0.05';
                watermark.style.zIndex = '0';
                watermark.style.width = '100%';
                watermark.style.maxWidth = '800px';
                watermark.style.top = '50%';
                watermark.style.left = '50%';
                watermark.style.transform = 'translate(-50%, -50%) rotate(-45deg)';
            }

            const previewBody = document.getElementById('previewBody');
            previewBody.innerHTML = '';
            previewBody.appendChild(previewContent);

            const printBtn = document.createElement('button');
            printBtn.className = 'btn btn-print';
            printBtn.style.position = 'fixed';
            printBtn.style.bottom = '20px';
            printBtn.style.right = '20px';
            printBtn.style.zIndex = '1001';
            printBtn.innerHTML = '<i class="fas fa-print"></i> Print Report';
            printBtn.onclick = function() {
                printReport();
            };
            previewBody.appendChild(printBtn);

            document.getElementById('previewModal').style.display = 'flex';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }

        function printReport() {

            closePreview();

            setTimeout(() => {
                window.print();
            }, 100);
        }

        function exportReport() {
            if (confirm('Export as PDF? The browser will open print dialog. Choose "Save as PDF" as printer.')) {
                printReport();
            }
        }

        function refreshPage() {
            window.location.reload();
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('patientsTable');
            
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const text = cell.textContent || cell.innerText;
                        if (text.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        }

        function filterTable() {
            const filter = document.getElementById('statusFilter').value;
            const table = document.getElementById('patientsTable');
            
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const statusCell = row.querySelector('td:nth-child(7)');
                
                if (statusCell) {
                    const statusBadge = statusCell.querySelector('.status-badge');
                    let status = '';
                    
                    if (statusBadge) {
                        status = statusBadge.classList.contains('status-treated') ? 'treated' : 'pending';
                    }
                    
                    if (filter === '' || status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {

            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchTable();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closePreview();
                }
            });

            console.log('Image exists: <?php echo $image_exists ? "Yes" : "No"; ?>');
            console.log('Image path: <?php echo $image_path; ?>');
        });

        document.addEventListener('keydown', function(e) {

            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                showPrintPreview();
            }

            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportReport();
            }

            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshPage();
            }
        });

        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target === modal) {
                closePreview();
            }
        }
