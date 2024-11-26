document.addEventListener('DOMContentLoaded', function () {
    // Event listener for printing the Daily Sales report
    document.getElementById('printDailySalesBtn').addEventListener('click', function () {
        printDailySalesReport();
    });

    // Event listener for generating PDF of the Daily Sales report
    document.getElementById('generateDailySalesBtn').addEventListener('click', function () {
        generateDailySalesReportPDF();
    });

    // Function to update the header dynamically based on the cashier
    function updateHeader(cashierName) {
        const headerElement = document.querySelector('.daily-sales-report h2');
        headerElement.textContent = `Daily Sales Report - ${cashierName}`;
    }

    // Simulate updating the header when table data changes (replace with real logic)
    document.getElementById('cashierSelector').addEventListener('change', function (e) {
        const selectedCashier = e.target.value; // Get selected cashier's name
        updateHeader(selectedCashier); // Update the header dynamically
    });

    // Function to print the Daily Sales report
    function printDailySalesReport() {
        const printDailySalesBtn = document.getElementById('printDailySalesBtn');
        const generateDailySalesBtn = document.getElementById('generateDailySalesBtn');
        const headingToHide = document.querySelector('.daily-sales-report h2');
        const cashierName = headingToHide.textContent;

        // Temporarily hide buttons and heading for print
        printDailySalesBtn.style.display = 'none';
        generateDailySalesBtn.style.display = 'none';

        // Get the current date
        const today = new Date();
        const date = today.toLocaleDateString(); // Format: MM/DD/YYYY

        const printContent = document.querySelector('.daily-sales-report table').outerHTML;
        const printWindow = window.open('', '', 'height=500,width=800');
        printWindow.document.write('<html><head><title>Daily Sales Report</title>');

        // Add Bootstrap for styling the table and custom styles
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>table { width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid black; } th, td { padding: 8px; text-align: center; border: 1px solid black; } h2 { text-align: center; margin-bottom: 10px; font-size: 16px } p { text-align: right; margin-top: 10px; }</style>');

        printWindow.document.write('</head><body>');
        printWindow.document.write(`<h2>Sheila Grocery Store</h2>`);
        printWindow.document.write(`<h2>${cashierName}</h2>`); // Dynamic cashier name in header
        printWindow.document.write(`<p>Date: ${date}</p>`);
        printWindow.document.write(printContent); // Include only the table
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();

        // Restore the hidden elements after printing
        printDailySalesBtn.style.display = 'block';
        generateDailySalesBtn.style.display = 'block';
    }

    // Function to generate PDF of the Daily Sales report
    function generateDailySalesReportPDF() {
        const printDailySalesBtn = document.getElementById('printDailySalesBtn');
        const generateDailySalesBtn = document.getElementById('generateDailySalesBtn');
        const headingElement = document.querySelector('.daily-sales-report h2');
        const cashierName = headingElement.textContent;

        // Temporarily hide buttons
        printDailySalesBtn.style.display = 'none';
        generateDailySalesBtn.style.display = 'none';

        // Add styles for PDF generation
        const style = document.createElement('style');
        style.innerHTML = `
            table { width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid black; }
            th, td { padding: 10px; text-align: center; border: 1px solid black !important; font-family: Arial, sans-serif; font-size: 14px;  box-sizing: border-box; }
            th { background-color: #f2f2f2; font-weight: bold; }
            h2 { font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 10px; }
            p { text-align: right; font-family: Arial, sans-serif; margin-top: 10px; margin-bottom: 0; font-size: 14px; }
        `;
        document.head.appendChild(style);

        const tempContainer = document.createElement('div');
        const storeName = document.createElement('h2');
        storeName.textContent = 'Sheila Grocery Store'; // Store name
        const h2 = document.createElement('h2');
        h2.textContent = cashierName; // Use dynamic header
        const date = document.createElement('p');
        date.textContent = `Date: ${new Date().toLocaleDateString()}`;
        const table = document.querySelector('.daily-sales-report table').cloneNode(true);

        tempContainer.appendChild(storeName);
        tempContainer.appendChild(h2);
        tempContainer.appendChild(date);
        tempContainer.appendChild(table);

        // Use the temporary container for generating the PDF
        html2pdf()
            .from(tempContainer)
            .set({
                filename: 'daily-sales-report.pdf',
                margin: 1,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            })
            .save()
            .then(() => {
                // Restore buttons after generating the PDF
                printDailySalesBtn.style.display = 'block';
                generateDailySalesBtn.style.display = 'block';
                document.head.removeChild(style); // Remove temporary styles
            });
    }
});
