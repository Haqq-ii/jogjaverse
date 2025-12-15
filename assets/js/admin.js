(function () {
  const sidebar = document.querySelector('.sidebar');
  const main = document.querySelector('.main');

  window.toggleSidebar = function () {
    if (!sidebar) return;
    sidebar.classList.toggle('show');
  };

  if (main && sidebar) {
    main.addEventListener('click', () => sidebar.classList.remove('show'));
  }

  // tandai menu aktif sesuai URL
  const links = document.querySelectorAll('.menu .item');
  const path = (window.location.pathname || '').replace(/\\/g, '/');
  links.forEach((link) => {
    const href = link.getAttribute('href') || '';
    if (href && path.endsWith(href.replace(/\\/g, '/'))) {
      link.classList.add('active');
    }
  });
})();
