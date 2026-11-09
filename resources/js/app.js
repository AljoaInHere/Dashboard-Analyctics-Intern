// === Chart.js Global Registration ===
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Expose ke window supaya bisa dipanggil langsung dari Blade
window.Chart = Chart;