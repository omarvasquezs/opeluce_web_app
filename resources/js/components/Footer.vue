<template>
  <footer class="opeluce-footer">
    <div class="container text-center py-3">
      <div class="footer-text">&copy; 2025 Clínica de Ojos D´ Opeluce S.A.C. | Todos los derechos reservados.</div>
    </div>
  </footer>
</template>

<script>
const baseUrl = window.location.origin;

export default {
  name: 'Footer'
};
</script>

<style scoped>
.opeluce-footer {
  background: #fff;
  border-top: 1px solid #eaeaea;
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100vw;
  z-index: 100;
  box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
}
.opeluce-footer-logo {
  height: 40px;
  width: auto;
  display: block;
  margin: 0 auto 8px auto;
}
.footer-text {
  color: #444;
  font-size: 15px;
  font-family: 'Montserrat', 'Arial', sans-serif;
}
</style>
