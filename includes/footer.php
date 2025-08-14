</div> <!-- /container -->

<footer class="animated-footer text-white text-center mt-auto">
    <div class="container py-1">
        <p class="fw-bold mb-0" style="font-size: 0.8rem;">
            &copy; <?= date('Y') ?> Expense Tracker | Created by <span class="text-warning">Shehan Hasantha</span>
        </p>

        <div class="social-icons my-1">
            <a href="https://github.com/ShehanRUSL" target="_blank" class="mx-2 text-white fs-5"><i
                    class="bi bi-github"></i></a>
            <a href="https://www.linkedin.com/in/shehan-hasantha-bb381b340" target="_blank"
                class="mx-2 text-white fs-5"><i class="bi bi-linkedin"></i></a>
            <a href="mailto:shehanhasantha10@gmail.com" class="mx-2 text-white fs-5"><i
                    class="bi bi-envelope-fill"></i></a>
        </div>

        <p class="small text-light mb-0" style="font-size: 0.7rem;">Made with 💻 PHP, MySQL & Bootstrap</p>
    </div>
</footer>

<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
    }

    .animated-footer {
        background: linear-gradient(-45deg, #007bff, #6610f2, #6f42c1, #20c997);
        background-size: 400% 400%;
        animation: gradientMove 10s ease infinite;
        padding: 0.4rem 0;
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .animated-footer .container {
        padding-top: 0.1rem;
        padding-bottom: 0.1rem;
    }

    .animated-footer p,
    .animated-footer .social-icons {
        margin: 0.1rem 0;
    }

    @keyframes gradientMove {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .animated-footer a:hover {
        text-decoration: none;
        color: #ffc107;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('expenseChart')?.getContext('2d');
    if (ctx) {
        const expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Expenses by Category',
                    data: <?= json_encode($totals) ?>,
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
</script>

</body>

</html>