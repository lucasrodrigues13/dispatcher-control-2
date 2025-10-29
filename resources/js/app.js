import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Configurações globais do Chart.js
Chart.defaults.font.family = "'Inter', 'Helvetica', 'Arial', sans-serif";
Chart.defaults.color = '#2c3e50';
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;
