document.addEventListener('DOMContentLoaded', () => {
  const nav = document.getElementById('mainNavbar');
  if (!nav) return;
  if (document.body.classList.contains('navbar-solid')) {
    nav.classList.add('navbar-scrolled');
    return;
  }

  const onScroll = () => {
    if (window.scrollY > 50) {
      nav.classList.add('navbar-scrolled');
    } else {
      nav.classList.remove('navbar-scrolled');
    }
  };

  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
});
