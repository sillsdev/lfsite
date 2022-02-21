import { expect, test, APIRequestContext, request, Page } from '@playwright/test';
import { getLoggedInPage } from './login';
import constants from '../app/testConstants.json';
import { jsonRpc } from './utils/json-rpc';

test.describe.only('Multiple users editing the same project', () => {
  let adminPage: Page;
  let memberPage: Page;
  const entryName: string = constants.testEntry2.lexeme.th.value;

  test.beforeEach(async ({browser}) => {
    const adminRequest = await request.newContext({ storageState: 'admin-storageState.json', baseURL: 'http://app-for-e2e' });
    const session = await getSession(adminRequest);
    session.projectSettings.config.pollUpdateIntervalMs = 10 * 1000;
    await updateProjectConfig(adminRequest, session.projectSettings.config);
    adminPage = await getLoggedInPage(browser, 'admin');
    memberPage = await getLoggedInPage(browser, 'member');
    await Promise.all([
      adminPage.goto('/app/projects'),
      memberPage.goto('/app/projects'),
    ]);
    await Promise.all([
      adminPage.locator(`div.listview a:has-text("${constants.testProjectName}")`).click(),
      memberPage.locator(`div.listview a:has-text("${constants.testProjectName}")`).click(),
    ]);
  });

  test('Edit data in one entry', async () => {
    await Promise.all([
      adminPage.locator(`#scrolling-entry-words-container >> text=${entryName}`).click(),
      memberPage.locator(`#scrolling-entry-words-container >> text=${entryName}`).click(),
    ]);
    await Promise.all([
      expect(await getField(adminPage, "Word", "th").inputValue()).toContain(constants.testEntry2.lexeme['th'].value),
      expect(await getField(memberPage, "Word", "tipa").inputValue()).toContain(constants.testEntry2.lexeme['th-fonipa'].value),
    ]);
    await Promise.all([
      getField(adminPage, "Word", "tipa").fill('tipa for Word from admin'),
      getField(memberPage, "Word", "th").fill('th for Word from member'),
    ]);
    await Promise.all([
      adminPage.screenshot({path: 'admin-filled-in.png'}),
      memberPage.screenshot({path: 'member-filled-in.png'}),
    ]);
    await Promise.all([
      adminPage.waitForTimeout(24 * 1000),
      memberPage.waitForTimeout(24 * 1000),
    ]);
    await Promise.all([
      adminPage.screenshot({path: 'admin-waited.png'}),
      memberPage.screenshot({path: 'member-waited.png'}),
    ]);
    // await Promise.all([
    //   expect(await getField(adminPage, "Word", "th").inputValue()).toContain('th for Word from member'),
    //   expect(await getField(memberPage, "Word", "tipa").inputValue()).toContain('tipa for Word from admin'),
    // ]);
  });
});

function getSession(requestContext: APIRequestContext) {
  return jsonRpc(requestContext, 'session_getSessionData');
}

function updateProjectConfig(requestContext: APIRequestContext, config: any) {
  return jsonRpc(requestContext, 'lex_configuration_update', [config, []]);
}

function getField(page: Page, fieldName: string, ws: string) {
  return page.locator(`div.dc-entry form:has-text("${fieldName}") .input-group:has(span.wsid:text-is("${ws}")) textarea`)
}
