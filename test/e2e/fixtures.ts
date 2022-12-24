import type { Browser, Page } from '@playwright/test';
import { test as base, APIRequestContext } from '@playwright/test';
import { users } from './constants';
import { getStorageStatePath, Project, ProjectTestService, UserDetails, UserTestService } from './utils';

export type Tab = Page;
export type E2EUsername = keyof typeof users;

const userTab = (user: UserDetails) => async ({ browser }: { browser: Browser }, use: (r: Tab) => Promise<void>) => {
  const storageState = getStorageStatePath(user);
  const context = await browser.newContext({ storageState });
  const tab = await context.newPage();
  await use(tab); // returns after the next test() completes
  // We have to close all the pages we open or else they stay open forever and leak into the results (traces, screenshots) of proceding tests.
  await context.close();
};

// The userTab fixture represents a browser tab (a "page" in Playwright terms) that's already logged in as that user
// The tab fixture represents a browser tab where nobody is logged in
// Note: "Tab" was chosen instead of "Page" to avoid confusion with Page Object Model classes like SiteAdminPage
export const test = base
  .extend<{
    adminTab: Tab,
    managerTab: Tab,
    memberTab: Tab,
    observerTab: Tab,
    tab: Page,
    userService: UserTestService,
    projectService: ProjectTestService,
  }>({
    adminTab: userTab(users.admin),
    managerTab: userTab(users.manager),
    memberTab: userTab(users.member),
    observerTab: userTab(users.observer), // override page & request to use baseUrl
    tab: async ({ page }: { page: Page }, use: (r: Page) => Promise<void>) => {
      await use(page);
    },
    userService: async ({ request }: { request: APIRequestContext }, use: (userService: UserTestService) => Promise<void>) => {
      const userService = new UserTestService(request);
      await use(userService);
    },
    projectService: async ({ request }: { request: APIRequestContext }, use: (projectService: ProjectTestService) => Promise<void>) => {
      const projectService = new ProjectTestService(request);
      await use(projectService);
    },
  });

/**
 * When called a LF project will be created before hand for each playwright test (i.e. test.beforeEach)
 * If lazy = true, a project will only be created when explicitly "accessed" by calling
 * the returned function. This is useful if not every test in the describe block really needs its own project.
 * @returns a function for accessing the project that was created for the current test
 */
export function projectPerTest(lazy: true): () => Promise<Project>
export function projectPerTest(lazy?: false): () => Project
export function projectPerTest(lazy?: boolean): () => Project | Promise<Project> {

  let currentTestProjectGetter: () => Promise<Project>;
  let currentTestProject: Project;

  test.beforeEach(async ({ projectService }, testInfo) => {
    currentTestProject = undefined;
    currentTestProjectGetter = () => projectService.initTestProjectForTest(testInfo, users.manager);
    if (!lazy) {
      currentTestProject = await projectService.initTestProjectForTest(testInfo, users.manager);
    }
  });

  if (lazy) {
    return async () => {
      currentTestProject ??= await currentTestProjectGetter();
      return currentTestProject;
    }
  } else {
    return () => currentTestProject;
  }
}
