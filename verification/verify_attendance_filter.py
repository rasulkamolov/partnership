from playwright.sync_api import sync_playwright
import time
import datetime

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # 1. Login
        print("Logging in...")
        page.goto("http://localhost:8080")
        if page.is_visible('input[name="user"]'):
            page.fill('input[name="user"]', "admin_updated")
            page.fill('input[name="pass"]', "newpass123")
            page.click('button[name="login"]')
            page.wait_for_selector('text=Boshqaruv Paneli')

        # 2. Setup Test Data (2 Groups, 1 Student each)
        # Determine today's schedule type
        dow = datetime.datetime.now().weekday() + 1 # 1=Mon
        schedule_type = 'odd' if dow % 2 != 0 else 'even'
        if dow == 7: schedule_type = 'sunday' # Not handled in code but whatever

        print(f"Today is {dow}, schedule type: {schedule_type}")

        # Create Group A
        grp_a = "GroupA_" + str(int(time.time()))
        page.goto("http://localhost:8080/?p=settings")
        page.fill('input[name="name"]', grp_a)
        page.fill('input[name="price"]', "100")
        page.click('button:has-text("Qo\'shish")')
        page.wait_for_load_state("networkidle")

        # Create Group B
        grp_b = "GroupB_" + str(int(time.time()))
        page.fill('input[name="name"]', grp_b)
        page.fill('input[name="price"]', "100")
        page.click('button:has-text("Qo\'shish")')
        page.wait_for_load_state("networkidle")

        # Enroll Student A in Group A
        page.goto("http://localhost:8080/?p=dashboard")
        st_a = "StudentA_" + str(int(time.time()))
        page.fill('input[name="st_name"]', st_a)
        page.select_option('select[name="st_school"]', index=1)
        # Select Group A
        page.reload() # Refresh groups
        page.fill('input[name="st_name"]', st_a)
        page.select_option('select[name="st_school"]', index=1)
        page.locator('select[name="st_group[]"]').first.select_option(label=grp_a)
        # Set schedule to match today
        page.locator('select[name="st_schedule[]"]').first.select_option(value=schedule_type)
        page.click('button[name="add_student"]')
        page.wait_for_load_state("networkidle")

        # Enroll Student B in Group B
        st_b = "StudentB_" + str(int(time.time()))
        page.fill('input[name="st_name"]', st_b)
        page.select_option('select[name="st_school"]', index=1)
        page.locator('select[name="st_group[]"]').first.select_option(label=grp_b)
        page.locator('select[name="st_schedule[]"]').first.select_option(value=schedule_type)
        page.click('button[name="add_student"]')
        page.wait_for_load_state("networkidle")

        # 3. Verify Attendance Page
        print("Verifying Attendance Page...")
        page.goto("http://localhost:8080/?p=attendance")

        # Check if dropdown exists
        if page.locator('select[name="group"]').count() > 0:
            print("Group dropdown found.")
        else:
            print("FAIL: Group dropdown not found.")
            return

        # Select Group A
        print(f"Selecting {grp_a}...")
        page.select_option('select[name="group"]', label=grp_a)
        page.wait_for_load_state("networkidle")

        # Verify Student A is visible, Student B is NOT
        if page.locator(f'text={st_a}').count() > 0:
             print("SUCCESS: Student A visible.")
        else:
             print("FAIL: Student A not visible.")

        if page.locator(f'text={st_b}').count() == 0:
             print("SUCCESS: Student B NOT visible (Correctly filtered).")
        else:
             print("FAIL: Student B is visible!")

        # Select Group B
        print(f"Selecting {grp_b}...")
        page.select_option('select[name="group"]', label=grp_b)
        page.wait_for_load_state("networkidle")

        # Verify Student B visible, Student A NOT
        if page.locator(f'text={st_b}').count() > 0:
             print("SUCCESS: Student B visible.")
        else:
             print("FAIL: Student B not visible.")

        if page.locator(f'text={st_a}').count() == 0:
             print("SUCCESS: Student A NOT visible (Correctly filtered).")
        else:
             print("FAIL: Student A is visible!")

        # Take screenshot
        page.screenshot(path="verification/attendance_group_filter.png")

    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
