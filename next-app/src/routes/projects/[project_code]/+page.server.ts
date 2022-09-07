import { get_activities } from './activities/+server'
import { get as get_project_info } from './meta/+server'

export async function load({ params: { project_code }, request: { headers }}) {
	const args = {
		project_code,
		cookie: headers.get('cookie'),
	}

	const project = await get_project_info(args)

	const last_30_days = {
		start_date: daysAgo(30),
		end_date: new Date(),
	}
	const activities = await get_activities({ ...last_30_days, ...args })

	return {
		project,
		activities,
	}
}

function daysAgo(num_days) {
	const today = new Date();
	const daysAgo = new Date(today.setDate(today.getDate() - num_days))

	return daysAgo
}
