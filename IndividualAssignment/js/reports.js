document.addEventListener('DOMContentLoaded', function() {
    const exportExcelBtn = document.getElementById('export-excel');
    const exportCsvBtn = document.getElementById('export-csv');
    const tasksTable = document.getElementById('tasks-table');

    // Function to convert table to worksheet
    function tableToWorksheet(table) {
        const rows = table.querySelectorAll('tr');
        const worksheet = [];

        // Extract headers
        const headers = Array.from(rows[0].querySelectorAll('th')).map(th => th.textContent);
        worksheet.push(headers);

        // Extract data rows
        Array.from(rows).slice(1).forEach(row => {
            const rowData = Array.from(row.querySelectorAll('td')).map(td => {
                // Remove any HTML elements and keep only text
                return td.textContent.trim();
            });
            worksheet.push(rowData);
        });

        return worksheet;
    }

    // Add click handlers with error handling
    [exportExcelBtn, exportCsvBtn].forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const format = button.id.split('-')[1];
            const url = '';

            try {
                if (format === 'excel') {
                    // First try Excel export
                    if (typeof XLSX === 'undefined') {
                        throw new Error('XLSX library not loaded');
                    }

                    const worksheet = tableToWorksheet(tasksTable);
                    const workbook = XLSX.utils.book_new();
                    const worksheet_name = 'TaskFlow_Tasks';

                    const ws = XLSX.utils.aoa_to_sheet(worksheet);
                    XLSX.utils.book_append_sheet(workbook, ws, worksheet_name);

                    // Generate and download the Excel file
                    XLSX.writeFile(workbook, `TaskFlow_Tasks_${new Date().toISOString().split('T')[0]}.xlsx`);
                } else if (format === 'csv') {
                    const worksheet = tableToWorksheet(tasksTable);
                    const csvContent = worksheet.map(row => row.join(',')).join('\n');

                    // Create a temporary blob and trigger download
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = window.URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.setAttribute('hidden', '');
                    a.setAttribute('href', url);
                    a.setAttribute('download', `TaskFlow_Tasks_${new Date().toISOString().split('T')[0]}.csv`);

                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            } catch (error) {
                console.error('Error exporting file:', error);
                alert(`An error occurred while exporting to ${format.toUpperCase()}. Please try again.`);
            }
        });
    });

    // Debug function to test XLSX functionality
    window.testXLSX = function() {
        if (typeof XLSX === 'undefined') {
            console.error('XLSX library is not loaded');
            return false;
        }
        return true;
    };
});