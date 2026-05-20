import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.js',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        brand: {
          50: '#F2F2F3',
          100: '#DADADC',
          200: '#C3C3C6',
          300: '#ABABB0',
          400: '#93939A',
          500: '#7C7C83',
          600: '#65656C',
          700: '#4F4F54',
          800: '#39393C',
          900: '#1E1E20',
          950: '#0C0C0D',
        },
      },
    },
  },

  plugins: [forms, typography],
}
