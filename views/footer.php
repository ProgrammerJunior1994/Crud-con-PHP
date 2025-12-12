        </div>
      </div>
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Sidebar Toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
    });

    // Active nav link
    document.addEventListener('DOMContentLoaded', function() {
      const currentPage = window.location.pathname.split('/').pop();
      const navLinks = document.querySelectorAll('.nav-link');
      
      navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
          link.classList.add('active');
        } else {
          link.classList.remove('active');
        }
      });
    });

    // Auto-update time
    function updateTime() {
      const now = new Date();
      document.getElementById('currentTime').textContent = now.toLocaleString('es-ES');
    }
    
    setInterval(updateTime, 1000);
    updateTime();
  </script>
</body>
</html>