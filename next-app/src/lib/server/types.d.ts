import type { HttpMethod } from '@sveltejs/kit/types/private'

type Rpc = {
	name: string,
	args?: string[] | object[],
	cookie?: string,
}

type FetchArgs = {
	url: string,
	method: HttpMethod,
	body: object,
	cookie?: string,
}

type SfResponse = {
	error?: {
		message: string
	},
	result?: any,
}

type LegacySession = {
	userId: string,
	userProjectRole: string,
}

type User = {
	id: string,
	role: string,
}