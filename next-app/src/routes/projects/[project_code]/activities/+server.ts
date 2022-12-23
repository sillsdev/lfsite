import { json } from '@sveltejs/kit'
import { sf } from '$lib/server/sf'
import type { Rpc } from '$lib/server/types'
import type { RequestHandler } from './$types'

export const GET = (async ({ params: { project_code }, request: { headers } }) => {
	const cookie = headers.get('cookie') || ''

	await sf({ name: 'set_project', args: [ project_code ], cookie })

	const activities = await fetch_activities({ cookie })

	return json(activities)
}) satisfies RequestHandler

// src/Api/Model/Shared/Dto/ActivityListDto.php
// src/Api/Model/Shared/Dto/ActivityListDto.php->ActivityListModel.__construct
export async function fetch_activities({ cookie, start_date, end_date }: ActivitiesInput) {
	const args: Rpc = {
		name: 'activity_list_dto_for_current_project',
		args: [
			{
				startDate: start_date,
				endDate: end_date,
				limit: start_date || end_date ? 50 : 0,
			},
		],
		cookie,
	}

	const { activity }: LegacyResult = await sf(args)

	return activity.map(transform)
}

function transform({ id, action, date, content }: LegacyActivity): Activity {
	return {
		id,
		action,
		date,
		user: content.user,
		entry: content.entry || '',
		fields: content.changes || [],
	}
}
