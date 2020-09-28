from needle.cases import NeedleTestCase
import time

class PetrKnapTest(NeedleTestCase):
    cleanup_on_success = True
    viewport_width = 1280
    viewport_height = 720

    def test_homepage(self):
        self.driver.get('https://petrknap.cz/')

        time.sleep(0.25)
        self.driver.find_element_by_css_selector('.cc-dismiss').click()
        time.sleep(1.5)

        self.assertScreenshot('header', 'header')

        sections = self.driver.find_elements_by_css_selector('section')
        for section in sections:
            self.assertScreenshot('#' + section.get_attribute('id'), section.get_attribute('id'))

        self.assertScreenshot('footer', 'footer')
