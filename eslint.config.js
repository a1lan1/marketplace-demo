import vue from 'eslint-plugin-vue'
import prettier from 'eslint-config-prettier'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'

export default defineConfigWithVueTs(
  prettier,
  vue.configs['flat/recommended'],
  vueTsConfigs.recommended,
  {
    ignores: ['vendor', 'node_modules', 'public', 'bootstrap/ssr', 'tailwind.config.js', 'resources/js/components/ui/*']
  },
  {
    rules: {
      '@typescript-eslint/no-unused-expressions': [
        'error',
        {
          allowShortCircuit: true,
          allowTernary: true
        }
      ],
      'vue/multi-word-component-names': 'off',
      '@typescript-eslint/comma-dangle': 'off',
      '@typescript-eslint/no-explicit-any': 'off',
      'comma-dangle': ['error', 'never'],
      'object-curly-spacing': ['error', 'always'],
      'quotes': ['error', 'single'],
      'keyword-spacing': ['error'],
      'space-before-function-paren': ['error', 'never'],
      'space-before-blocks': ['error', 'always'],
      'no-multiple-empty-lines': ['error', {
        'max': 1
      }],
      'no-trailing-spaces': ['error'],
      'padding-line-between-statements': [
        'error',
        { 'blankLine': 'always', 'prev': '*', 'next': 'return' }
      ],
      'semi': ['error', 'never'],
      'key-spacing': 'error',
      'indent': ['error', 2],
      'vue/require-explicit-emits': ['off'],
      'no-useless-catch': ['off']
    }
  }
)
