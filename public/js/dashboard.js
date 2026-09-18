document.addEventListener('DOMContentLoaded', function () {
  const elDados = document.getElementById('dados-dashboard');
  if (!elDados) return;

  const dados = JSON.parse(elDados.textContent);

  // ---------- Gráfico de linha: evolução das vendas ----------
  const canvasVendas = document.getElementById('grafico-vendas');
  if (canvasVendas && dados.evolucaoVendas.length > 0) {
    new Chart(canvasVendas, {
      type: 'line',
      data: {
        labels: dados.evolucaoVendas.map(d => d.data),
        datasets: [{
          label: 'Vendas',
          data: dados.evolucaoVendas.map(d => d.total),
          borderColor: '#22c55e',
          backgroundColor: 'rgba(34,197,94,0.08)',
          borderWidth: 2.5,
          tension: 0.35,
          fill: true,
          pointRadius: 3,
          pointBackgroundColor: '#22c55e',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: '#e6eaf0' },
            ticks: { callback: (v) => new Intl.NumberFormat('pt-PT').format(v) }
          },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // ---------- Gráfico donut: métodos de pagamento ----------
  const canvasMetodos = document.getElementById('grafico-metodos');
  if (canvasMetodos && dados.metodos.length > 0) {
    new Chart(canvasMetodos, {
      type: 'doughnut',
      data: {
        labels: dados.metodos.map(m => m.rotulo),
        datasets: [{
          data: dados.metodos.map(m => m.total),
          backgroundColor: dados.metodos.map(m => m.cor),
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: { legend: { display: false } }
      }
    });
  }
});
