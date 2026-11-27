(() => {
    const form = document.querySelector('[data-report-export-form]');
    if (!form || !window.Chart) {
        return;
    }

    const maxImageLength = 1200000;
    const maxTotalLength = 4000000;
    const colorPaths = [
        ['color'],
        ['plugins', 'legend', 'labels', 'color'],
        ['plugins', 'title', 'color'],
        ['scales', 'x', 'ticks', 'color'],
        ['scales', 'y', 'ticks', 'color'],
        ['scales', 'x', 'title', 'color'],
        ['scales', 'y', 'title', 'color'],
    ];

    function withPrintColors(chart, capture) {
        const saved = [];
        for (const path of colorPaths) {
            let node = chart.options;
            for (const key of path.slice(0, -1)) {
                if (!node[key] || typeof node[key] !== 'object') {
                    node = null;
                    break;
                }
                node = node[key];
            }
            if (!node) {
                continue;
            }
            const key = path[path.length - 1];
            saved.push([node, key, node[key]]);
            node[key] = '#212529';
        }
        chart.update('none');
        try {
            return capture();
        } finally {
            for (const [node, key, value] of saved) {
                if (value === undefined) {
                    delete node[key];
                } else {
                    node[key] = value;
                }
            }
            chart.update('none');
        }
    }

    function capture(canvas) {
        const chart = typeof window.Chart.getChart === 'function' ? window.Chart.getChart(canvas) : null;
        if (chart) {
            return withPrintColors(chart, () => canvas.toDataURL('image/png'));
        }
        return canvas.toDataURL('image/png');
    }

    form.addEventListener('submit', () => {
        form.querySelectorAll('[data-report-chart-image]').forEach((input) => input.remove());
        let total = 0;
        document.querySelectorAll('.cafebara-report-chart canvas').forEach((canvas) => {
            if (!canvas.width || !canvas.height) {
                return;
            }
            try {
                const image = capture(canvas);
                if (!image || image.length > maxImageLength || total + image.length > maxTotalLength) {
                    return;
                }
                total += image.length;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'charts[]';
                input.value = image;
                input.dataset.reportChartImage = '1';
                form.appendChild(input);
            } catch (error) {
                return;
            }
        });
    });
})();
