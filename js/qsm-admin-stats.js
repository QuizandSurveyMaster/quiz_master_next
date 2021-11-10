window.onload = function(){
  var graph_ctx = document.getElementById("graph_canvas").getContext("2d");
  window.stats_graph = new Chart(graph_ctx, {
    type: 'line',
    data: {
        labels: qsm_admin_stats.labels,
        datasets: [{
            label: 'Quiz Submissions', // Name the series
            data: qsm_admin_stats.value, // Specify the data values array
            fill: false,
            borderColor: '#2196f3', // Add custom color border (Line)
            backgroundColor: '#2196f3', // Add custom color background (Points and Fill)
            borderWidth: 1 // Specify bar border width
        }]},
        options: {
      responsive: true, // Instruct chart js to respond nicely.
      maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height
    }
});
}
				