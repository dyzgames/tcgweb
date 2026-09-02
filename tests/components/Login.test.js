import { describe, it, expect, beforeEach, vi } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/svelte'
import Login from '../../src/components/Login.svelte'

describe('Login.svelte', () => {
  beforeEach(() => {
    global.fetch = vi.fn()
    localStorage.clear()
  })

  it('renderiza el formulario con sus inputs y el botón', () => {
    render(Login)
    expect(screen.getByPlaceholderText('Usuario')).toBeTruthy()
    expect(screen.getByPlaceholderText('Contraseña')).toBeTruthy()
    expect(screen.getByText('LOGIN')).toBeTruthy()
  })

  it('muestra error y no llama a la API si los campos están vacíos', async () => {
    render(Login)

    await fireEvent.click(screen.getByText('LOGIN'))

    expect(await screen.findByText(/requeridos/i)).toBeTruthy()
    expect(global.fetch).not.toHaveBeenCalled()
  })

  it('envía el login y guarda el token en localStorage', async () => {
    global.fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        success: true,
        session_token: 'tok123',
        user_id: 1,
        username: 'admin',
        is_admin: true,
        data: {
          session_token: 'tok123',
          user_id: 1,
          username: 'admin',
          is_admin: true
        }
      })
    })

    render(Login)

    await fireEvent.input(screen.getByPlaceholderText('Usuario'), {
      target: { value: 'admin' }
    })
    await fireEvent.input(screen.getByPlaceholderText('Contraseña'), {
      target: { value: 'admin123' }
    })
    await fireEvent.click(screen.getByText('LOGIN'))

    await vi.waitFor(() => expect(global.fetch).toHaveBeenCalledTimes(1))
    await vi.waitFor(() =>
      expect(localStorage.getItem('session_token')).toBe('tok123')
    )
  })

  it('muestra el mensaje de error devuelto por el backend', async () => {
    global.fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        success: false,
        error: 'Credenciales incorrectas'
      })
    })

    render(Login)

    await fireEvent.input(screen.getByPlaceholderText('Usuario'), {
      target: { value: 'admin' }
    })
    await fireEvent.input(screen.getByPlaceholderText('Contraseña'), {
      target: { value: 'wrong' }
    })
    await fireEvent.click(screen.getByText('LOGIN'))

    expect(await screen.findByText(/credenciales incorrectas/i)).toBeTruthy()
  })
})
