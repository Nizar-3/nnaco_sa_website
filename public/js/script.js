// Custom JavaScript
console.log('nnaco_sa system loaded.');

/**
 * Escapes HTML characters to prevent XSS.
 */
function escapeChatHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Formats inline Markdown (bold, italic, code, links).
 */
function formatInlineMarkdown(text) {
    if (!text) return '';
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`([^`]+)`/g, '<code class="bg-light text-danger px-1.5 py-0.5 rounded small border">$1</code>')
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary fw-medium text-decoration-underline" target="_blank">$1</a>');
}

/**
 * Checks if a string contains a markdown table.
 */
function hasMarkdownTable(text) {
    if (!text) return false;
    return /\|.+[\r\n]+\|?\s*[-:]+[-| :]*\|?/.test(text);
}

/**
 * Formats a table cell value with smart badges, currency, or inline formatting.
 */
function formatChatTableCell(cellText, colHeader = '') {
    const trimmed = cellText ? cellText.trim() : '';
    if (!trimmed) return '-';

    const cleanRaw = trimmed.replace(/\*\*/g, '');

    // 1. Status Badges
    if (/^(stok tersedia|tersedia|ready|aktif|active|paid|lunas|selesai|berhasil|completed)$/i.test(cleanRaw)) {
        return `<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle fw-semibold px-2 py-1"><i class="bi bi-check-circle-fill me-1 text-success"></i>${escapeChatHtml(cleanRaw)}</span>`;
    }
    if (/^(stok habis|habis|sold out|nonaktif|inactive|unpaid|batal|gagal|cancelled)$/i.test(cleanRaw)) {
        return `<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle fw-semibold px-2 py-1"><i class="bi bi-x-circle-fill me-1 text-danger"></i>${escapeChatHtml(cleanRaw)}</span>`;
    }
    if (/^(sisa\s+\d+|menipis|pending|menunggu|diproses|dikemas|processing)/i.test(cleanRaw)) {
        return `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold px-2 py-1"><i class="bi bi-exclamation-circle-fill me-1 text-warning"></i>${escapeChatHtml(cleanRaw)}</span>`;
    }

    // 2. User Roles
    if (/^(Admin|Operator|Owner|Customer)$/i.test(cleanRaw)) {
        let badgeClass = 'bg-secondary-subtle text-secondary-emphasis';
        if (/Admin/i.test(cleanRaw)) badgeClass = 'bg-danger-subtle text-danger-emphasis border border-danger-subtle';
        else if (/Operator/i.test(cleanRaw)) badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        else if (/Owner/i.test(cleanRaw)) badgeClass = 'bg-dark text-white';
        else if (/Customer/i.test(cleanRaw)) badgeClass = 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
        return `<span class="badge ${badgeClass} fw-semibold px-2 py-1">${escapeChatHtml(cleanRaw)}</span>`;
    }

    // 3. IDs and Numbers (No / ID)
    if (/^#?\d+$/.test(cleanRaw) && /id|no/i.test(colHeader)) {
        return `<span class="fw-semibold text-secondary font-monospace" style="font-size: 0.8rem;">${escapeChatHtml(cleanRaw)}</span>`;
    }

    // 4. Currency and Price
    if (/(rp|\b\d{1,3}(?:[.,\s]\d{3})+(?:\b|\.00))/i.test(cleanRaw) && /harga|total|omzet|subtotal|price/i.test(colHeader)) {
        let val = cleanRaw;
        // Clean spaces in numbers like 15 000 -> 15.000
        val = val.replace(/(\d+)\s+(\d{3})/, '$1.$2');
        if (!/^rp/i.test(val) && /^\d/.test(val)) val = 'Rp ' + val;
        return `<span class="fw-bold text-dark font-monospace">${escapeChatHtml(val)}</span>`;
    }

    return formatInlineMarkdown(escapeChatHtml(trimmed));
}

/**
 * Parses Full Markdown with clean, responsive Bootstrap 5 Table support.
 */
function renderChatMarkdown(markdownText) {
    if (!markdownText) return '';

    // Normalize line endings
    const text = markdownText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const lines = text.split('\n');
    const outputBlocks = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];
        const trimmed = line.trim();

        // 1. Table Detection & Parsing
        if (trimmed.includes('|') && i + 1 < lines.length) {
            const nextTrimmed = lines[i + 1].trim();
            const isSeparator = /^\|?(\s*:?-+:?\s*\|)+\s*:?-+:?\s*\|?$/.test(nextTrimmed);

            if (isSeparator) {
                // Parse Header row
                const rawHeaders = trimmed.split('|').map(c => c.trim());
                if (trimmed.startsWith('|') && rawHeaders.length > 0 && rawHeaders[0] === '') rawHeaders.shift();
                if (trimmed.endsWith('|') && rawHeaders.length > 0 && rawHeaders[rawHeaders.length - 1] === '') rawHeaders.pop();
                const headerCells = rawHeaders;

                // Parse Alignments from separator row
                const rawAligns = nextTrimmed.split('|').map(c => c.trim());
                if (nextTrimmed.startsWith('|') && rawAligns.length > 0 && rawAligns[0] === '') rawAligns.shift();
                if (nextTrimmed.endsWith('|') && rawAligns.length > 0 && rawAligns[rawAligns.length - 1] === '') rawAligns.pop();

                const alignments = rawAligns.map((c, idx) => {
                    if (c.startsWith(':') && c.endsWith(':')) return 'center';
                    if (c.endsWith(':')) return 'end';
                    if (c.startsWith(':')) return 'start';
                    // Auto-infer alignment based on header text
                    const header = headerCells[idx] || '';
                    if (/no|id|status|stok|qty|jumlah/i.test(header)) return 'center';
                    if (/harga|total|omzet|price/i.test(header)) return 'center';
                    return 'start';
                });

                i += 2; // Move past header and separator rows

                // Collect Table Body Rows
                const rows = [];
                while (i < lines.length && lines[i].trim().includes('|')) {
                    const rowLine = lines[i].trim();
                    if (!rowLine) break;

                    const rawCells = rowLine.split('|').map(c => c.trim());
                    if (rowLine.startsWith('|') && rawCells.length > 0 && rawCells[0] === '') rawCells.shift();
                    if (rowLine.endsWith('|') && rawCells.length > 0 && rawCells[rawCells.length - 1] === '') rawCells.pop();

                    rows.push(rawCells);
                    i++;
                }

                // Construct responsive Bootstrap 5 HTML Table
                let tableHtml = '<div class="table-responsive my-3 chat-table-container">';
                tableHtml += '<table class="table table-sm table-striped table-hover align-middle mb-0 chat-table">';
                
                // Header
                tableHtml += '<thead><tr>';
                headerCells.forEach((th, idx) => {
                    const alignClass = alignments[idx] ? `text-${alignments[idx]}` : 'text-start';
                    tableHtml += `<th class="py-2 px-3 fw-bold ${alignClass}">${formatInlineMarkdown(escapeChatHtml(th))}</th>`;
                });
                tableHtml += '</tr></thead>';

                // Body
                tableHtml += '<tbody>';
                rows.forEach(row => {
                    tableHtml += '<tr>';
                    headerCells.forEach((th, idx) => {
                        const cellVal = row[idx] !== undefined ? row[idx] : '';
                        const alignClass = alignments[idx] ? `text-${alignments[idx]}` : 'text-start';
                        tableHtml += `<td class="py-2 px-3 ${alignClass}">${formatChatTableCell(cellVal, th)}</td>`;
                    });
                    tableHtml += '</tr>';
                });
                tableHtml += '</tbody></table></div>';

                outputBlocks.push(tableHtml);
                continue;
            }
        }

        // 2. Headings (#, ##, ###)
        if (/^###\s+(.+)$/.test(trimmed)) {
            const hText = trimmed.replace(/^###\s+/, '');
            outputBlocks.push(`<h6 class="fw-bold text-dark mt-2 mb-1" style="font-size: 0.95rem;">${formatInlineMarkdown(escapeChatHtml(hText))}</h6>`);
            i++;
            continue;
        }
        if (/^##\s+(.+)$/.test(trimmed)) {
            const hText = trimmed.replace(/^##\s+/, '');
            outputBlocks.push(`<h6 class="fw-bolder text-dark mt-3 mb-1" style="font-size: 1rem;">${formatInlineMarkdown(escapeChatHtml(hText))}</h6>`);
            i++;
            continue;
        }
        if (/^#\s+(.+)$/.test(trimmed)) {
            const hText = trimmed.replace(/^#\s+/, '');
            outputBlocks.push(`<h5 class="fw-bolder text-dark mt-3 mb-2">${formatInlineMarkdown(escapeChatHtml(hText))}</h5>`);
            i++;
            continue;
        }

        // 3. Bullet lists (*, -)
        if (/^[\-\*]\s+(.+)$/.test(trimmed)) {
            const listItems = [];
            while (i < lines.length && /^[\-\*]\s+(.+)$/.test(lines[i].trim())) {
                const itemText = lines[i].trim().replace(/^[\-\*]\s+/, '');
                listItems.push(`<li class="mb-1">${formatInlineMarkdown(escapeChatHtml(itemText))}</li>`);
                i++;
            }
            outputBlocks.push(`<ul class="ps-3 mb-2" style="font-size: 0.88rem; line-height: 1.5;">${listItems.join('')}</ul>`);
            continue;
        }

        // 4. Numbered lists (1. , 2. )
        if (/^\d+\.\s+(.+)$/.test(trimmed)) {
            const listItems = [];
            while (i < lines.length && /^\d+\.\s+(.+)$/.test(lines[i].trim())) {
                const itemText = lines[i].trim().replace(/^\d+\.\s+/, '');
                listItems.push(`<li class="mb-1">${formatInlineMarkdown(escapeChatHtml(itemText))}</li>`);
                i++;
            }
            outputBlocks.push(`<ol class="ps-3 mb-2" style="font-size: 0.88rem; line-height: 1.5;">${listItems.join('')}</ol>`);
            continue;
        }

        // 5. Empty line
        if (!trimmed) {
            outputBlocks.push('');
            i++;
            continue;
        }

        // 6. Regular text paragraph
        outputBlocks.push(`<p class="mb-2" style="line-height: 1.55;">${formatInlineMarkdown(escapeChatHtml(trimmed))}</p>`);
        i++;
    }

    // Join and trim excessive empty gaps
    return outputBlocks.filter((b, idx, arr) => b !== '' || (idx > 0 && arr[idx - 1] !== '')).join('');
}

// Attach to window so it is always globally available in browser
if (typeof window !== 'undefined') {
    window.escapeChatHtml = escapeChatHtml;
    window.formatInlineMarkdown = formatInlineMarkdown;
    window.hasMarkdownTable = hasMarkdownTable;
    window.renderChatMarkdown = renderChatMarkdown;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        escapeChatHtml,
        formatInlineMarkdown,
        hasMarkdownTable,
        renderChatMarkdown
    };
}

