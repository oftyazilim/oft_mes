// import apps from './apps'
import sistem from './sistem'
import planlama from './planlama'
import dashboard from './dashboard'
import forms from './forms'
import misc from './misc'
import pages from './pages'
import tables from './tables'
import uiElements from './ui-elements'
import type { HorizontalNavItems } from '@layouts/types'

export default [...dashboard, ...planlama, ...sistem] as HorizontalNavItems;
