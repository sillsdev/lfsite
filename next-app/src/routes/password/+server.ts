import { json } from '@sveltejs/kit'
import { throwError } from '$lib/error'
import { sf } from '$lib/fetch/server'

export async function PUT({ request }) {
	const { password, password_confirm } = await request.json()

	if (! password) {
		throwError('Password is required', 400)
	}
	if (password !== password_confirm) {
		throwError('Passwords do not match', 400)
	}

	const cookie = request.headers.get('cookie')

	const { userId } = await sf({
		name: 'session_getSessionData',
		cookie,
	})

	if (! userId) {
		throwError('User unknown', 404)
	}

	await sf({
		name: 'change_password',
		args: [userId, password],
		cookie,
	})

	return json({})
}
