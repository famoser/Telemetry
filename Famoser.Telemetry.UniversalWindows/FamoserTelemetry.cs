using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging;
using Famoser.Telemetry.UniversalWindows.Logger;

namespace Famoser.Telemetry.UniversalWindows
{
    public class FamoserTelemetry
    {
        public static void Initialize(string url, string applicationId)
        {
            LogHelper.Instance.OverwriteLogger(new TelemetryLogger(url, applicationId));
        }
    }
}
